<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Codefog\TagsBundle\Model\TagModel;
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\FilesModel;
use Contao\ImageSizeModel;
use Contao\Message;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use HeimrichHannot\MediaLibraryBundle\Event\BeforeCreateImageDownloadEvent;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Model\Collection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Translation\Translator;

class ProductContainer
{
    const TYPE_FILE = 'file';
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';

    const TYPES
        = [
            self::TYPE_FILE,
            self::TYPE_IMAGE,
            self::TYPE_VIDEO,
        ];

    const CFG_TAG_ASSOCIATION_TABLE = 'tl_cfg_tag_ml_product';
    const CFG_TAG_ASSOCIATION_TAG_FIELD = 'cfg_tag_id';
    const CFG_TAG_ASSOCIATION_PRODUCT_FIELD = 'ml_product_id';

    /**
     * @var ModelUtil
     */
    protected $modelUtil;

    /**
     * @var DcaUtil
     */
    protected $dcaUtil;

    /**
     * @var FileUtil
     */
    protected $fileUtil;
    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;
    /**
     * @var \HeimrichHannot\UtilsBundle\String\StringUtil
     */
    private $stringUtil;
    /**
     * @var ContainerUtil
     */
    private $containerUtil;
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        Translator $translator,
        ModelUtil $modelUtil,
        DcaUtil $dcaUtil,
        FileUtil $fileUtil,
        DatabaseUtil $databaseUtil,
        \HeimrichHannot\UtilsBundle\String\StringUtil $stringUtil,
        ContainerUtil $containerUtil,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->modelUtil = $modelUtil;
        $this->dcaUtil = $dcaUtil;
        $this->fileUtil = $fileUtil;
        $this->databaseUtil = $databaseUtil;
        $this->stringUtil = $stringUtil;
        $this->containerUtil = $containerUtil;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function updateTagAssociations(DataContainer $dc): void
    {
        $source = $GLOBALS['TL_DCA']['tl_ml_product']['fields']['tags']['eval']['tagsManager'];
        $tags = $this->databaseUtil->findResultsBy(TagModel::getTable(), ['source=?'], [$source]);

        if (!$tags->numRows) {
            return;
        }

        while ($tags->next()) {
            $tagId = (int) $tags->id;

            if (!$this->tagIsInUse($tagId)) {
                $this->databaseUtil->delete(TagModel::getTable(), 'id=?', [$tagId]);
            }
        }
    }

    public function deleteTagAssociations(DataContainer $dc, int $id): void
    {
        $tagAssociations = $this->databaseUtil->findResultsBy(self::CFG_TAG_ASSOCIATION_TABLE, ['ml_product_id=?'],
            [$id]);

        if (!$tagAssociations->numRows) {
            return;
        }

        while ($tagAssociations->next()) {
            $tagId = (int) $tagAssociations->{self::CFG_TAG_ASSOCIATION_TAG_FIELD};
            $associationId = (int) $tagAssociations->{self::CFG_TAG_ASSOCIATION_PRODUCT_FIELD};

            if (!$this->tagIsInUse($tagId)) {
                $this->databaseUtil->delete(TagModel::getTable(), 'id=?', [$tagId]);
            }

            $this->databaseUtil->delete(self::CFG_TAG_ASSOCIATION_TABLE, self::CFG_TAG_ASSOCIATION_TAG_FIELD.'=? AND '.self::CFG_TAG_ASSOCIATION_PRODUCT_FIELD.'=?', [$tagId, $associationId]);
        }
    }

    public function listChildren($row)
    {
        return '<div class="tl_content_left">'.($row['title'] ?: $row['id']).'</div>';
    }

    public function setType($table, $insertID, $set, DataContainer $dc)
    {
        if ($insertID && null !== ($productArchive = $this->getProductArchive($insertID))) {
            Database::getInstance()->prepare('UPDATE tl_ml_product SET type=? WHERE id=?')->execute($productArchive->type,
                $insertID);
        }
    }

    public function addAdditionalFields(DataContainer $dc)
    {
        if (null === ($product = $this->getProduct($dc->id))) {
            return;
        }

        if (null === ($productArchive = $this->modelUtil->findModelInstanceByPk('tl_ml_product_archive', $product->pid))) {
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

    public function setCopyright(DataContainer $dc)
    {
        if (!$dc->activeRecord || !$dc->activeRecord->file) {
            return;
        }

        $file = StringUtil::deserialize($dc->activeRecord->file, true);

        if (empty($file)) {
            return;
        }

        $model = FilesModel::findByUuid($file[0]);

        if (null === $model) {
            return;
        }

        $versions = new Versions('tl_files', $model->id);
        $versions->initialize();

        $model->copyright = $dc->activeRecord->copyright ?? null;
        $model->save();

        $versions->create();
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
            return $dc->activeRecord->copyright;
        }

        return $dc->activeRecord->copyright ? $dc->activeRecord->copyright : $model->copyright;
    }

    /**
     * Generate download.
     *
     * @throws \Exception
     */
    public function generateDownloadItems(DataContainer $dc)
    {
        if ($dc->activeRecord->doNotCreateDownloadItems || !$dc->activeRecord->file) {
            return;
        }

        $this->doDeleteDownloads($dc, [
            'keepManuallyAdded' => true,
        ]);

        $this->createDownloadItems($dc);
    }

    public function deleteDownloads(DataContainer $dc, int $undoId)
    {
        $this->doDeleteDownloads($dc, [
            'addConfirmationMessage' => true,
        ]);
    }

    public function doDeleteDownloads(DataContainer $dc, array $options = [])
    {
        $addConfirmationMessage = $options['addConfirmationMessage'] ?? false;

        if (null === ($downloads = $this->getDownloadItems($dc, $options))) {
            return;
        }

        if (null === ($product = $this->getProduct($dc->id))) {
            return;
        }

        foreach ($downloads as $i => $download) {
            // get file model of download for later deletion
            $downloadFile = $this->fileUtil->getFileFromUuid($download->file);

            // delete model
            if (null !== $download) {
                $download->delete();
            }

            // keep the original files
            if (!$download->imageSize) {
                if ($addConfirmationMessage) {
                    Message::addConfirmation($GLOBALS['TL_LANG']['MSC']['contaoMediaLibraryBundle']['messageOriginalFileKept']);
                }

                continue;
            }

            // delete file
            if (null !== $downloadFile) {
                $downloadFile->delete();
            }
        }
    }

    public function checkPermission()
    {
        $user = \Contao\BackendUser::getInstance();
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

        $id = \strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;

        // Check current action
        switch (\Contao\Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!\strlen(\Contao\Input::get('pid')) || !\in_array(\Contao\Input::get('pid'), $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create ml_product items in ml_product archive ID '.\Contao\Input::get('pid').'.');
                }

                break;

            case 'cut':
            case 'copy':
                if (!\in_array(\Contao\Input::get('pid'), $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' ml_product item ID '.$id.' to ml_product archive ID '.\Contao\Input::get('pid').'.');
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

                if (!\in_array($objArchive->pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' ml_product item ID '.$id.' of ml_product archive ID '.$objArchive->pid.'.');
                }

                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!\in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access ml_product archive ID '.$id.'.');
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
                if (\strlen(\Contao\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "'.\Contao\Input::get('act').'".');
                } elseif (!\in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access ml_product archive ID '.$id.'.');
                }

                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (\strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), ('1' === \Contao\Input::get('state')),
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

        return '<a href="'.Controller::addToUrl($href).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'
            .$attributes.'>'.\Image::getHtml($icon, $label,
                'data-state="'.($row['published'] ? 1 : 0).'"').'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->activeRecord->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_ml_product']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_ml_product::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish ml_product item ID '.$intId.'.');
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
        if (\is_array($GLOBALS['TL_DCA']['tl_ml_product']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                } elseif (\is_callable($callback)) {
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
        if (\is_array($GLOBALS['TL_DCA']['tl_ml_product']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['config']['onsubmit_callback'] as $callback) {
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
     * @throws \Exception
     */
    public function generateAlias(DataContainer $dc)
    {
        if (null === ($product = $this->getProduct($dc->id))) {
            return;
        }

        if ($product->alias) {
            return;
        }

        $alias = $this->dcaUtil->generateAlias(
            $dc->activeRecord->alias,
            $dc->activeRecord->id,
            'tl_ml_product',
            $dc->activeRecord->title
        );

        Database::getInstance()->prepare('UPDATE tl_ml_product SET tl_ml_product.alias=? WHERE tl_ml_product.id=?')->execute($alias,
            $dc->activeRecord->id);
    }

    protected function tagIsInUse(int $id): int
    {
        $associations = $this->databaseUtil->findResultsBy(self::CFG_TAG_ASSOCIATION_TABLE, [self::CFG_TAG_ASSOCIATION_TAG_FIELD.'=?'], [$id]);

        return $associations->numRows;
    }

    protected function modifyTagAssociations(string $table, Result $tagAssociations): void
    {
        $source = $GLOBALS['TL_DCA']['tl_ml_product']['fields']['tags']['eval']['tagManager'];

        while ($tagAssociations->next()) {
            // delete tag if not in use by another entity
            $associationsFromOtherEntities = $this->databaseUtil->findResultsBy($table, ['cfg_tag_id=?'],
                [$tagAssociations->cfg_tag_id]);

            if ($associationsFromOtherEntities->numRows) {
                continue;
            }

            $this->databaseUtil->delete('tl_cfg_tag', 'tl_cfg_tag.id=? AND tl_cfg_tag.source=?', [$tagAssociations->cfg_tag_id, $source]);
        }
    }

    /**
     * @return Collection|Model|null
     */
    protected function getDownloadItems(DataContainer $dc, array $options = [])
    {
        $columns = ['tl_ml_download.pid=?'];
        $values = [$dc->id];

        if (isset($options['keepManuallyAdded']) && $options['keepManuallyAdded']) {
            $columns[] = 'tl_ml_download.author=?';
            $values[] = 0;
        }

        return $this->modelUtil->findModelInstancesBy('tl_ml_download', $columns, $values);
    }

    protected function getProduct(int $id): ?Model
    {
        return $this->modelUtil->findModelInstanceByPk('tl_ml_product', $id);
    }

    protected function getExifConfiguration(Model $archive, DataContainer $dc): array
    {
        $exifData = $dc->activeRecord->overrideExifData ? $dc->activeRecord->exifData : $archive->exifData;

        return StringUtil::deserialize($exifData, true);
    }

    /**
     * create download items.
     *
     * @throws \Exception
     */
    protected function createDownloadItems(DataContainer $dc, bool $isAdditional = false)
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
            $newDc = new DC_Table_Utils('tl_ml_product');
            $newDc->id = $dc->id;
            $newDc->activeRecord = $dc->activeRecord;

            foreach (StringUtil::deserialize($dc->activeRecord->additionalFiles, true) as $file) {
                $newDc->activeRecord->file = $file;

                $this->createDownloadItems($dc, true);
            }
        }
    }

    /**
     * Create image download items that will be resized.
     *
     * @throws \Exception
     */
    protected function createImageDownloadItems(FilesModel $file, DataContainer $dc, Model $archiveModel, int $originalDownload = 0, bool $isAdditional = false)
    {
        if (empty($sizes = $this->getSizes($archiveModel, $dc))) {
            return;
        }

        $imageFactory = System::getContainer()->get('contao.image.image_factory');

        foreach ($sizes as $size) {
            if (null === ($sizeModel = $this->modelUtil->findModelInstanceByPk('tl_image_size', $size))) {
                continue;
            }

            if (!$this->isResizable($file, $sizeModel)) {
                continue;
            }

            // compose filename
            $sizeName = $this->fileUtil->sanitizeFileName($sizeModel->name);

            $targetFilename = $this->stringUtil->removeTrailingString('\.'.$file->extension,
                    $file->name).'_'.$sizeName.'.'.$file->extension;

            // compose path
            $targetFile = $this->containerUtil->getProjectDir().\DIRECTORY_SEPARATOR.\dirname($file->path).\DIRECTORY_SEPARATOR.$targetFilename;

            $resizeImage = $imageFactory->create($this->containerUtil->getProjectDir().\DIRECTORY_SEPARATOR.$file->path,
                $size, $targetFile);

            $this->eventDispatcher->dispatch(BeforeCreateImageDownloadEvent::NAME, new BeforeCreateImageDownloadEvent(
                $resizeImage, $file, $targetFilename, $size
            ));

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
        $imageSize = getimagesize($this->containerUtil->getProjectDir().\DIRECTORY_SEPARATOR.$file->path);

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

        if (null === ($productArchive = $this->modelUtil->findModelInstanceByPk('tl_ml_product_archive', $product->pid))) {
            return null;
        }

        return $productArchive;
    }

    /**
     * create the DownloadModel for the image size.
     *
     * @param ImageSizeModel $size
     *
     * @throws \Exception
     */
    protected function createDownloadItem(
        string $path,
        DataContainer $dc,
        int $originalDownload = 0,
        bool $keepProductName = false,
        ImageSizeModel $size = null,
        bool $isAdditional = false
    ) {
        $data = [];

        $path = str_replace($this->containerUtil->getProjectDir().\DIRECTORY_SEPARATOR,
            '', $path);

        if (null === ($file = FilesModel::findByPath($path))) {
            $file = Dbafs::addResource(urldecode($path));
        }

        if (null !== $size) {
            $data['imageSize'] = $size->id;
        }

        $data['tstamp'] = $data['dateAdded'] = time();

        $data['title'] = $this->getDownloadTitle($dc, $keepProductName, $size);
        $data['pid'] = $dc->activeRecord->id;
        $data['file'] = $file->uuid;

        $data['published'] = true;

        $this->databaseUtil->insert('tl_ml_download', [
            'imageSize' => $data['imageSize'] ?? 0,
            'originalDownload' => $originalDownload,
            'tstamp' => $data['tstamp'],
            'dateAdded' => $data['dateAdded'],
            'title' => $data['title'],
            'pid' => $data['pid'],
            'file' => $data['file'],
            'isAdditional' => $isAdditional ? 1 : '',
            'published' => $data['published'],
        ]);

        // return download id
        $download = Database::getInstance()->prepare('SELECT id FROM tl_ml_download WHERE pid=? AND imageSize=? AND file=UNHEX(?)')
            ->limit(1)->execute(
                $data['pid'], $data['imageSize'] ?? 0, bin2hex($data['file'])
            );

        return $download->id;
    }

    protected function getDownloadTitle(
        DataContainer $dc,
        bool $keepProductName = false,
        ImageSizeModel $size = null
    ): string {
        $title = $dc->activeRecord->title;

        if (null === $size) {
            return $keepProductName ? $title : $this->translator->trans('huh.mediaLibrary.downloadTitle.original');
        }

        return $keepProductName ? $this->translator->trans('huh.mediaLibrary.downloadTitle.sizeWithProductTitle', ['{title}' => $title, '{size}' => $size->name]) : $size->name;
    }
}
