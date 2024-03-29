<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Codefog\TagsBundle\Model\TagModel;
use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\Slug\Slug;
use Contao\Database;
use Contao\Database\Result;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\FilesModel;
use Contao\Image;
use Contao\ImageSizeModel;
use Contao\Input;
use Contao\Message;
use Contao\Model;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;
use Contao\Versions;
use Exception;
use HeimrichHannot\MediaLibraryBundle\Event\BeforeCreateImageDownloadEvent;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Driver\DC_Table_Utils;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Model\Collection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductContainer
{
    public const TYPE_FILE = 'file';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    public const TYPES = [
        self::TYPE_FILE,
        self::TYPE_IMAGE,
        self::TYPE_VIDEO,
    ];

    public const CFG_TAG_ASSOCIATION_TABLE = 'tl_cfg_tag_ml_product';
    public const CFG_TAG_ASSOCIATION_TAG_FIELD = 'cfg_tag_id';
    public const CFG_TAG_ASSOCIATION_PRODUCT_FIELD = 'ml_product_id';

    protected DcaUtil $dcaUtil;
    protected FileUtil $fileUtil;
    protected array $bundleConfig;
    private DatabaseUtil $databaseUtil;
    private TranslatorInterface $translator;
    private EventDispatcherInterface $eventDispatcher;
    private Utils $utils;
    private ParameterBagInterface $parameterBag;
    private Security $security;

    public function __construct(
        array $bundleConfig,
        TranslatorInterface $translator,
        DcaUtil $dcaUtil,
        FileUtil $fileUtil,
        DatabaseUtil $databaseUtil,
        EventDispatcherInterface $eventDispatcher,
        Utils $utils,
        ParameterBagInterface $parameterBag,
        Security $security,
        private Slug $slug
    ) {
        $this->bundleConfig = $bundleConfig;
        $this->dcaUtil = $dcaUtil;
        $this->fileUtil = $fileUtil;
        $this->databaseUtil = $databaseUtil;
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->utils = $utils;
        $this->parameterBag = $parameterBag;
        $this->security = $security;
    }

    /**
     * @Callback(table="tl_ml_product", target="fields.alias.save")
     */
    public function onFieldsAliasSaveCallback($varValue, DataContainer $dc)
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            return Database::getInstance()->prepare("SELECT id FROM tl_ml_product WHERE alias=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        // Generate alias if there is none
        if (!$varValue)
        {
            $varValue = $this->slug->generate($dc->activeRecord->title, null, $aliasExists);
        }
        elseif (preg_match('/^[1-9]\d*$/', $varValue))
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasNumeric'], $varValue));
        }
        elseif ($aliasExists($varValue))
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;
    }

    public function updateTagAssociations(DataContainer $dc): void
    {
        $source = $GLOBALS['TL_DCA']['tl_ml_product']['fields']['tags']['eval']['tagsManager'];
        $tags = $this->databaseUtil->findResultsBy(TagModel::getTable(), ['source=?'], [$source]);

        if (!$tags->numRows) {
            return;
        }

        $ids = [];
        $tagsInUse = $this->getTagsInUse();

        while ($tags->next()) {
            $tagId = (int) $tags->id;

            if (!\in_array($tagId, $tagsInUse)) {
                $ids[] = $tagId;
            }
        }

        if (!empty($ids)) {
            $this->databaseUtil->delete(TagModel::getTable(), 'id IN ('.implode(',', $ids).')', []);
        }
    }

    public function deleteTagAssociations(DataContainer $dc, int $undoId): void
    {
        $tagAssociations = $this->databaseUtil->findResultsBy(self::CFG_TAG_ASSOCIATION_TABLE, ['ml_product_id=?'],
            [$dc->id]);

        if (!$tagAssociations->numRows) {
            return;
        }

        while ($tagAssociations->next()) {
            $tagId = (int) $tagAssociations->{self::CFG_TAG_ASSOCIATION_TAG_FIELD};
            $productId = (int) $tagAssociations->{self::CFG_TAG_ASSOCIATION_PRODUCT_FIELD};

            $tagUsedByOtherRecord = $this->databaseUtil->findOneResultBy(self::CFG_TAG_ASSOCIATION_TABLE, [
                self::CFG_TAG_ASSOCIATION_TAG_FIELD.'=?',
                self::CFG_TAG_ASSOCIATION_PRODUCT_FIELD.'!=?',
            ], [
                $tagId,
                $dc->id,
            ]);

            if ($tagUsedByOtherRecord->numRows < 1) {
                $this->databaseUtil->delete(TagModel::getTable(), 'id=?', [$tagId]);
            }

            $this->databaseUtil->delete(self::CFG_TAG_ASSOCIATION_TABLE,
                self::CFG_TAG_ASSOCIATION_TAG_FIELD.'=? AND '.self::CFG_TAG_ASSOCIATION_PRODUCT_FIELD.'=?', [$tagId, $productId]
            );
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

        if (null === ($productArchive = $this->utils->model()->findModelInstanceByPk('tl_ml_product_archive', $product->pid))) {
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

        return $dc->activeRecord->copyright ?: $model->copyright;
    }

    /**
     * Generate download.
     *
     * @throws Exception
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

        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
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
            throw new AccessDeniedException('Not enough permissions to publish/unpublish ml_product item ID '.$intId.'.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare('SELECT * FROM tl_ml_product WHERE id=?')->limit(1)->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new Versions('tl_ml_product', $intId);
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
        $database->prepare("UPDATE tl_ml_product SET tstamp=?, published=? WHERE id=?")
            ->execute($time, $blnVisible ? '1' : '', $intId);

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
            $newDc = new DC_Table_Utils('tl_ml_product');
            $newDc->id = $dc->id;
            $newDc->activeRecord = $dc->activeRecord;

            foreach (StringUtil::deserialize($dc->activeRecord->additionalFiles, true) as $file) {
                $newDc->activeRecord->file = $file;

                $this->createDownloadItems($dc, true);
            }
        }
    }

    protected function tagIsInUse(int $id): int
    {
        $associations = $this->databaseUtil->findResultsBy(self::CFG_TAG_ASSOCIATION_TABLE, [self::CFG_TAG_ASSOCIATION_TAG_FIELD.'=?'], [$id]);

        return $associations->numRows;
    }

    protected function getTagsInUse()
    {
        $records = $this->databaseUtil->findResultsBy(self::CFG_TAG_ASSOCIATION_TABLE, null, null);

        if ($records->numRows < 1) {
            return [];
        }

        return $records->fetchEach('cfg_tag_id');
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

        return $this->utils->model()->findModelInstancesBy('tl_ml_download', $columns, $values);
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
    protected function createImageDownloadItems(FilesModel $file, DataContainer $dc, Model $archiveModel, int $originalDownload = 0, bool $isAdditional = false)
    {
        if (empty($sizes = $this->getSizes($archiveModel, $dc))) {
            return;
        }

        $imageFactory = System::getContainer()->get('contao.image.image_factory');

        foreach ($sizes as $size) {
            if (null === ($sizeModel = $this->utils->model()->findModelInstanceByPk('tl_image_size', $size))) {
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

    /**
     * create the DownloadModel for the image size.
     *
     * @param ImageSizeModel $size
     *
     * @throws Exception
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

        $path = str_replace($this->parameterBag->get('kernel.project_dir').\DIRECTORY_SEPARATOR,
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
