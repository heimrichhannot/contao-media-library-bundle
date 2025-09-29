<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\Image;
use Contao\ImageSizeModel;
use Contao\Input;
use Contao\Model;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use HeimrichHannot\MediaLibraryBundle\Event\BeforeCreateImageDownloadEvent;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @deprecated To be removed in v2.
 */
class ProductContainer
{
    public const TABLE = ItemContainer::TABLE;

    public function __construct(
        protected array $bundleConfig,
        protected DcaUtil $dcaUtil,
        protected FileUtil $fileUtil,
        private DatabaseUtil $databaseUtil,
        private EventDispatcherInterface $eventDispatcher,
        private Utils $utils,
        private ParameterBagInterface $parameterBag,
        private Security $security,
    ) {}

    public function checkPermission(): void
    {
        /** @var BackendUser $user */
        if (!($user = $this->security->getUser() instanceof BackendUser)) {
            throw new AccessDeniedException('You have no permission to do this.');
        }

        $database = \Contao\Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!\is_array($user->contao_media_library_bundles) || empty($user->contao_media_library_bundles)) {
            $root = [0];
        } else {
            $root = $user->contao_media_library_bundles;
        }

        $id = \strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

        // Check current action
        switch (Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!\strlen(Input::get('pid')) || !\in_array(Input::get('pid'), $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to create ml_product items in ml_product archive ID '. Input::get('pid').'.');
                }

                break;

            case 'cut':
            case 'copy':
                if (!\in_array(Input::get('pid'), $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to '. Input::get('act').' ml_product item ID '.$id.' to ml_product archive ID '. Input::get('pid').'.');
                }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare('SELECT pid FROM tl_ml_product WHERE id=?')->limit(1)->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid ml_product item ID '.$id.'.');
                }

                if (!\in_array($objArchive->pid, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to '. Input::get('act').' ml_product item ID '.$id.' of ml_product archive ID '.$objArchive->pid.'.');
                }

                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!\in_array($id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access ml_product archive ID '.$id.'.');
                }

                $objArchive = $database->prepare('SELECT id FROM tl_ml_product WHERE pid=?')->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new AccessDeniedException('Invalid ml_product archive ID '.$id.'.');
                }

                /** @var SessionInterface $session */
                $session = System::getContainer()->get('session');

                $sessionData = $session->all();
                $sessionData['CURRENT']['IDS'] = array_intersect($sessionData['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($sessionData);

                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Invalid command "'. Input::get('act').'".');
                } elseif (!\in_array($id, $root, true)) {
                    throw new AccessDeniedException('Not enough permissions to access ml_product archive ID '.$id.'.');
                }

                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        /** @var BackendUser $user */
        $user = $this->security->getUser();

        if (\strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), ('1' === Input::get('state')),
                (@func_get_arg(12) ?: null));
            Controller::redirect(System::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_ml_product::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="'.Controller::addToUrl($href).'&rt='.RequestToken::get().'" title="'.StringUtil::specialchars($title).'"'
            .$attributes.'>'.Image::getHtml($icon, $label,
                'data-state="'.($row['published'] ? 1 : 0).'"').'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        /** @var BackendUser $user */
        $user = $this->security->getUser();
        $database = \Contao\Database::getInstance();
        $table = self::TABLE;

        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA'][self::TABLE]['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA'][self::TABLE]['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess("{self::TABLE}::published", 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to publish/unpublish ml_product item ID '.$intId.'.');
        }

        // Set the current record
        if ($dc)
        {
            $objRow = $database->prepare("SELECT * FROM {$table} WHERE id=?")->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions(self::TABLE, $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA'][self::TABLE]['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA'][self::TABLE]['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE {$table} SET tstamp=?, published=? WHERE id=?")
            ->execute($time, $blnVisible ? '1' : '', $intId);

        if ($dc) {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA'][self::TABLE]['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA'][self::TABLE]['config']['onsubmit_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }

    /**
     * create download items.
     *
     * @throws Exception
     */
    public function createDownloadItems(DataContainer $dc, bool $isAdditional = false)
    {
        $uuid = $dc->activeRecord->file;

        if (null === ($file = $this->fileUtil->getFileFromUuid($uuid))) {
            return;
        }

        $fileModel = $file->getModel();

        if (null === ($archiveModel = $this->getProductArchive($dc->activeRecord->id))) {
            return;
        }

        // create a download for the original file
        $downloadId = $this->createDownloadItem($fileModel->path, $dc, 0, $archiveModel->keepProductTitleForDownloadItems, null, $isAdditional);

        // create image size-based downloads
        if (\in_array($fileModel->extension, explode(',', Config::get('validImageTypes')), true)) {
            $this->createImageDownloadItems($fileModel, $dc, $archiveModel, $downloadId, $isAdditional);
        }

        // create image size-based downloads for the additional files, as well
        if ($dc->activeRecord->addAdditionalFiles && !$isAdditional) {
            // create a new dc using DC_Table_Utils so that no callbacks are called
            $newDc = new DC_Table_Utils(ItemModel::getTable());
            $newDc->id = $dc->id;
            $newDc->activeRecord = $dc->activeRecord;

            foreach (StringUtil::deserialize($dc->activeRecord->additionalFiles, true) as $file) {
                $newDc->activeRecord->file = $file;

                $this->createDownloadItems($dc, true);
            }
        }
    }

    protected function getProduct(int $id): ?Model
    {
        return $this->utils->model()->findModelInstanceByPk('tl_ml_product', $id);
    }

    protected function getExifConfiguration(Model $archive, DataContainer $dc): array
    {
        $exifData = $dc->activeRecord->overrideExifData ? $dc->activeRecord->exifData : $archive->exifData;

        return StringUtil::deserialize($exifData, true);
    }

    /**
     * Create image download items that will be resized.
     *
     * @throws Exception
     */
    protected function createImageDownloadItems(
        FilesModel    $file,
        DataContainer $dc,
        Model         $archiveModel,
        int           $originalDownload = 0,
        bool          $isAdditional = false
    ): void {
        if (empty($sizes = $this->getSizes($archiveModel, $dc))) {
            return;
        }

        if (!$imageFactory = System::getContainer()->get('contao.image.image_factory')) {
            throw new \Exception('The Contao Image Factory is not available.');
        }

        foreach ($sizes as $size)
        {
            if (!$sizeModel = ImageSizeModel::findByPk($size)) {
                continue;
            }

            if (!$this->isResizable($file, $sizeModel)) {
                continue;
            }

            // compose filename
            $targetFilename = $file->name.'_'.$sizeModel->name.'.'.$file->extension;

            if ($this->bundleConfig['sanitize_download_filenames'] ?? false) {
                $targetFilename = $this->fileUtil->sanitizeFileName($targetFilename);
            }

            // compose path
            $projectDir = $this->parameterBag->get('kernel.project_dir');
            $targetFile = $projectDir .\DIRECTORY_SEPARATOR.\dirname($file->path).\DIRECTORY_SEPARATOR.$targetFilename;

            $resizeImage = $imageFactory->create($projectDir.\DIRECTORY_SEPARATOR.$file->path,
                $size, $targetFile);

            $this->eventDispatcher->dispatch(
                new BeforeCreateImageDownloadEvent($resizeImage, $file, $targetFilename, $size),
                BeforeCreateImageDownloadEvent::NAME
            );

            $this->createDownloadItem($resizeImage->getPath(), $dc, $originalDownload, $archiveModel->keepProductTitleForDownloadItems,
                $sizeModel, $isAdditional);
        }
    }

    /**
     * @return array
     */
    protected function getSizes(Model $archiveModel, DataContainer $dc)
    {
        return StringUtil::deserialize(
            $this->dcaUtil->getOverridableProperty(
                'imageSizes',
                [
                    $archiveModel,
                    $dc->activeRecord,
                ]
            ),
            true
        );
    }

    /**
     * check if the size of the image is bigger than the resize measures.
     *
     * @return bool
     */
    protected function isResizable(FilesModel $file, ImageSizeModel $size)
    {
        $imageSize = getimagesize($this->parameterBag->get('kernel.project_dir').\DIRECTORY_SEPARATOR.$file->path);

        if ($size->width > $imageSize[0] && $size->height > $imageSize[1]) {
            return false;
        }

        return true;
    }

    /**
     * get Model for product.
     *
     * @return \Contao\Model\Collection|Model|null
     */
    protected function getProductArchive(int $id)
    {
        if (null === ($product = $this->getProduct($id))) {
            return null;
        }

        if (null === ($productArchive = $this->utils->model()->findModelInstanceByPk('tl_ml_product_archive', $product->pid))) {
            return null;
        }

        return $productArchive;
    }
}
