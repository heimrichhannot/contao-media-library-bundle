<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Backend;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Database;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\File;
use Contao\FilesModel;
use Contao\Folder;
use Contao\ImageSizeModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Registry\DownloadRegistry;
use HeimrichHannot\MediaLibraryBundle\Registry\ProductArchiveRegistry;
use HeimrichHannot\MediaLibraryBundle\Registry\ProductRegistry;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class Product
{
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';

    const TYPES = [
        self::TYPE_FILE,
        self::TYPE_IMAGE,
    ];

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var ProductArchiveRegistry
     */
    protected $productArchiveRegistry;

    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    /**
     * @var DcaUtil
     */
    protected $dcaUtil;

    public function __construct(
        ContaoFrameworkInterface $framework,
        ProductArchiveRegistry $productArchiveRegistry,
        ProductRegistry $productRegistry,
        DownloadRegistry $downloadRegistry,
        ModelUtil $modelUtil,
        DcaUtil $dcaUtil
    ) {
        $this->framework = $framework;
        $this->productArchiveRegistry = $productArchiveRegistry;
        $this->productRegistry = $productRegistry;
        $this->downloadRegistry = $downloadRegistry;
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
    }

    public function listChildren($row)
    {
        return '<div class="tl_content_left">'.($row['title'] ?: $row['id']).'</div>';
    }

    public function setType(DataContainer $dc)
    {
        if ($dc->id && null !== ($productArchive = $this->getProductArchive($dc->id))) {
            Database::getInstance()->prepare('UPDATE tl_ml_product SET type=? WHERE id=?')->execute($productArchive->type, $dc->id);
        }
    }

    public function addAdditionalFields(DataContainer $dc)
    {
        if (null === ($product = $this->productRegistry->findByPk($dc->id))) {
            return;
        }

        if (null === ($productArchive = $this->productArchiveRegistry->findByPk($product->pid))) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_ml_product'];

        $additionalFields = StringUtil::deserialize($productArchive->additionalFields, true);

        if (!empty($additionalFields)) {
            $dca['palettes'][$product->type] = str_replace(
                '{additional_fields_legend}',
                '{additional_fields_legend},'.implode(',', $additionalFields),
                $dca['palettes'][$product->type]
            );
        }
    }

    public function setCopyright($varValue, DataContainer $dc)
    {
        if (!$dc->activeRecord || !$dc->activeRecord->file) {
            return '';
        }

        $file = StringUtil::deserialize($dc->activeRecord->file, true);

        if (empty($file)) {
            return '';
        }

        $model = FilesModel::findByUuid($file[0]);

        if (null === $model) {
            return '';
        }

        $versions = new Versions('tl_files', $model->id);
        $versions->initialize();

        $model->copyright = $varValue;
        $model->save();

        $versions->create();

        return '';
    }

    public function getCopyright($value, DataContainer $dc)
    {
        if (!$dc->activeRecord || !$dc->activeRecord->file) {
            return '';
        }

        $file = StringUtil::deserialize($dc->activeRecord->file, true);

        if (empty($file)) {
            return '';
        }

        $model = FilesModel::findByUuid($file[0]);

        if (null === $model) {
            return '';
        }

        return $model->copyright;
    }

    /**
     * Generate download.
     *
     * @param DataContainer $dc
     *
     * @throws \Exception
     */
    public function generateDownloadItems(DataContainer $dc)
    {
        if ($dc->activeRecord->doNotCreateDownloadItems || !$dc->activeRecord->file) {
            return;
        }

        $this->doDeleteDownloads($dc->id, [
            'keepManuallyAdded' => true,
            'keepFolder' => true,
            'keepOriginal' => true,
        ]);

        $this->createDownloadItems($dc);
    }

    public function deleteDownloads(DataContainer $dc, int $undoId)
    {
        $this->doDeleteDownloads($dc->id);
    }

    public function doDeleteDownloads(int $id, array $options = [])
    {
        $downloads = isset($options['keepManuallyAdded']) && $options['keepManuallyAdded']
            ? $this->downloadRegistry->findGeneratedImages($id)
            : $this->downloadRegistry->findBy(
                'pid',
                $id
            );

        if (null === $downloads || (null === ($productArchive = $this->getProductArchive($id)))) {
            return;
        }

        $folder = null;

        foreach ($downloads as $i => $download) {
            // delete model
            $download->delete();

            // delete file
            $files = StringUtil::deserialize($download->file, true);

//            if (count($files) < 1 || (isset($options['keepOriginal']) && $options['keepOriginal'] && (!))) {
//                continue;
//            }

            if (null !== ($file = System::getContainer()->get('huh.utils.file')->getFileFromUuid($files[0]))) {
                if (!$folder) {
                    $folder = str_replace(
                        System::getContainer()->get('huh.utils.container')->getProjectDir().DIRECTORY_SEPARATOR,
                        '',
                        $file->dirname
                    );
                }

                $file->delete();
            }
        }

        $canDeleteFolder = !(isset($options['keepFolder']) && $options['keepFolder']) && null !== $folder
                           && ($productArchive->uploadFolderUserPattern
                               || $productArchive->addProductPatternToUploadFolder
                                  && $productArchive->uploadFolderProductPattern);

        if ($canDeleteFolder) {
            if (null !== ($folder = new Folder($folder))) {
                $folder->delete();
            }
        }
    }

    /**
     * Sets the current date as the date added -> usually used on submit.
     *
     * @param DataContainer $dc
     */
    public function copyFile($insertId, DataContainer $dc)
    {
        if (null === ($oldProduct = $this->modelUtil->findModelInstanceByPk('tl_ml_product', $dc->id))) {
            return;
        }

        if (null === ($newProduct = $this->modelUtil->findModelInstanceByPk('tl_ml_product', $insertId))) {
            return;
        }

        $file = StringUtil::deserialize($oldProduct->file, true);

        if (empty($file)) {
            return;
        }

        $file = $file[0];

        if (null === ($fileObj = (System::getContainer()->get('huh.utils.file')->getFileFromUuid($file)))) {
            return;
        }

        $newFilename =
            System::getContainer()->get('huh.media_library.backend.product_archive')->doGetUploadFolder($newProduct).'/'.$fileObj->name;

        $fileObj->copyTo($newFilename);

        if (null === ($newFileModel = ($this->modelUtil->findOneModelInstanceBy('tl_files', ['path=?'], [$newFilename])))) {
            return;
        }

        $newProduct->file = $newFileModel->uuid;
        $newProduct->save();
    }

    public function checkPermission()
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!is_array($user->contao_media_library_bundles) || empty($user->contao_media_library_bundles)) {
            $root = [0];
        } else {
            $root = $user->contao_media_library_bundles;
        }

        $id = strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Contao\Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(\Contao\Input::get('pid')) || !in_array(\Contao\Input::get('pid'), $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to create ml_product items in ml_product archive ID '.\Contao\Input::get('pid').'.'
                    );
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Contao\Input::get('pid'), $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to '.\Contao\Input::get('act').' ml_product item ID '.$id.' to ml_product archive ID '
                        .\Contao\Input::get('pid').'.'
                    );
                }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare('SELECT pid FROM tl_ml_product WHERE id=?')->limit(1)->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid ml_product item ID '.$id.'.');
                }

                if (!in_array($objArchive->pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to '.\Contao\Input::get('act').' ml_product item ID '.$id.' of ml_product archive ID '
                        .$objArchive->pid.'.'
                    );
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to access ml_product archive ID '.$id.'.'
                    );
                }

                $objArchive = $database->prepare('SELECT id FROM tl_ml_product WHERE pid=?')->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid ml_product archive ID '.$id.'.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');

                $session = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);
                break;

            default:
                if (strlen(\Contao\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "'.\Contao\Input::get('act').'".');
                } elseif (!in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to access ml_product archive ID '.$id.'.'
                    );
                }
                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), ('1' === \Contao\Input::get('state')), (@func_get_arg(12) ?: null));
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

        return '<a href="'.Controller::addToUrl($href).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'
               .$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="'.($row['published'] ? 1 : 0).'"').'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_ml_product::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                'Not enough permissions to publish/unpublish ml_product item ID '.$intId.'.'
            );
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare('SELECT * FROM tl_ml_product WHERE id=?')->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_ml_product', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_ml_product SET tstamp=$time, published='".($blnVisible ? '1' : "''")."' WHERE id=?")->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['config']['onsubmit_callback'] as $callback) {
                if (is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }

    public function generateAlias(DataContainer $dc)
    {
        if (null === ($product = System::getContainer()->get('huh.media_library.product_registry')->findByPk($dc->activeRecord->id))) {
            return null;
        }

        $product->alias = System::getContainer()->get('huh.utils.dca')->generateAlias(
            $dc->activeRecord->alias,
            $dc->activeRecord->id,
            'tl_ml_product',
            $dc->activeRecord->title
        );
        $product->save();
    }

    /**
     * create download items.
     *
     * @param DataContainer $dc
     *
     * @throws \Exception
     */
    protected function createDownloadItems(DataContainer $dc)
    {
        $file = StringUtil::deserialize($dc->activeRecord->file, true)[0];

        /** @var FilesModel $fileObj */
        $fileObj = $this->framework->getAdapter(FilesModel::class)->findByUuid($file);

        if (in_array($fileObj->extension, explode(',', Config::get('validImageTypes')), true)) {
            $this->createImageDownloadItems($fileObj, $dc);
        }
    }

    /**
     * Create image download items that will be resized.
     *
     * @param FilesModel    $file
     * @param string        $extension
     * @param DataContainer $dc
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function createImageDownloadItems(FilesModel $file, DataContainer $dc)
    {
        if (null === ($productArchive = $this->getProductArchive($dc->id))) {
            return false;
        }

        $fileUtil = System::getContainer()->get('huh.utils.file');
        $stringUtil = System::getContainer()->get('huh.utils.string');
        $containerUtil = System::getContainer()->get('huh.utils.container');
        $imageFactory = System::getContainer()->get('contao.image.image_factory');

        $sizes = StringUtil::deserialize(
            System::getContainer()->get('huh.utils.dca')->getOverridableProperty(
                'imageSizes',
                [
                    $productArchive,
                    $dc->activeRecord,
                ]
            ),
            true
        );

        // create a download for the original image
        $this->createDownloadItem($file->path, $dc);

        foreach ($sizes as $size) {
            if (null === ($sizeModel = $this->framework->getAdapter(ImageSizeModel::class)->findByPk($size))) {
                continue;
            }

            if (!$this->isResizable($file, $sizeModel)) {
                continue;
            }

            // compose filename
            $sizeName = $fileUtil->sanitizeFileName($sizeModel->name);

            $targetFilename = $stringUtil->removeTrailingString('\.'.$file->extension, $file->name).'_'.$sizeName.'.'.$file->extension;

            // compose path
            $targetFile = $containerUtil->getProjectDir().DIRECTORY_SEPARATOR.dirname($file->path).DIRECTORY_SEPARATOR.$targetFilename;

            $resizeImage = $imageFactory->create($containerUtil->getProjectDir().DIRECTORY_SEPARATOR.$file->path, $size, $targetFile);

            $this->createDownloadItem($resizeImage->getPath(), $dc, $sizeModel);
        }
    }

    /**
     * check if the size of the image is bigger than the resize measures.
     *
     * @param FilesModel     $file
     * @param ImageSizeModel $size
     *
     * @return bool
     */
    protected function isResizable(FilesModel $file, ImageSizeModel $size)
    {
        $imageSize = getimagesize(System::getContainer()->get('huh.utils.container')->getProjectDir().DIRECTORY_SEPARATOR.$file->path);

        if ($size->width > $imageSize[0] && $size->height > $imageSize[1]) {
            return false;
        }

        return true;
    }

    /**
     * get ProductArchiveModel for product.
     *
     * @param int $id
     *
     * @return \Contao\Model\Collection|ProductArchiveModel|null
     */
    protected function getProductArchive(int $id)
    {
        if (null === ($product = $this->productRegistry->findByPk($id))) {
            return null;
        }

        if (null === ($productArchive = $this->productArchiveRegistry->findByPk($product->pid))) {
            return null;
        }

        return $productArchive;
    }

    /**
     * create the DownloadModel for the image size.
     *
     * @param string         $path
     * @param DataContainer  $dc
     * @param ImageSizeModel $size
     *
     * @throws \Exception
     */
    protected function createDownloadItem(string $path, DataContainer $dc, ImageSizeModel $size = null)
    {
        $downloadItem = new DownloadModel();
        $title = $dc->activeRecord->title;

        $path = str_replace(System::getContainer()->get('huh.utils.container')->getProjectDir().DIRECTORY_SEPARATOR, '', $path);

        if (null === ($file = FilesModel::findByPath($path))) {
            $file = Dbafs::addResource(urldecode($path));
        }

        if (null !== $size) {
            $title .= ' ('.$size->name.')';
            $downloadItem->imageSize = $size->id;
        }

        $downloadItem->tstamp = $downloadItem->dateAdded = time();

        $downloadItem->title = $title;
        $downloadItem->pid = $dc->id;
        $downloadItem->file = $file->uuid;

        $downloadItem->published = true;

        $downloadItem->save();
    }
}
