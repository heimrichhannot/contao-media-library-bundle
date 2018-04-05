<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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
use Contao\FilesModel;
use Contao\ImageSizeModel;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
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

    public function setType(DataContainer $dc)
    {
        if (null !== ($productArchive = $this->getProductArchive($dc->id))) {
            Database::getInstance()->prepare('UPDATE tl_ml_product SET type=? WHERE id=?')->execute($productArchive->type, $dc->id);
        }
    }

    /**
     * delete old download items before progressing.
     *
     * @param DataContainer $dc
     *
     * @throws \Exception
     */
    public function generateDownloadItems(DataContainer $dc)
    {
        if (null === ($product = $this->modelUtil->findModelInstanceByPk('tl_ml_product', $dc->id))) {
            return;
        }

        if ($product->doNotCreateDownloadItems) {
            return;
        }

        $files = StringUtil::deserialize($product->uploadedFiles, true);

        if (empty($files)) {
            return;
        }

        $this->cleanGeneratedDownloadItems($product, true);
        $this->createDownloadItems($product);
    }

    /**
     * generate tags from values of product.
     *
     * @param DataContainer $dc
     *
     * @return bool
     */
    public function generateTags(DataContainer $dc)
    {
        if (null === ($productArchive = $this->getProductArchive($dc->id))) {
            return false;
        }

        if (null === ($product = $this->productRegistry->findByPk($dc->id))) {
            return false;
        }

        $fieldsForTags = StringUtil::deserialize($productArchive->fieldsForTags, true);

        // use all fields if none is defined
        if (empty($fieldsForTags)) {
            $fieldsForTags = System::getContainer()
                ->get('huh.utils.dca')
                ->getFields('tl_ml_product',
                    ['inputTypes' => ['text', 'textarea', 'select', 'multifileupload', 'checkbox', 'tagsinput'], 'localizeLabels' => false]);
        }

        $tags = [];

        foreach ($fieldsForTags as $field) {
            $this->addToTags($product, $field, $tags, $dc);
        }

        if (isset($product->tags) && !in_array('tags', $fieldsForTags, true)) {
            $tags = array_unique(array_merge($tags, StringUtil::deserialize($product->tags, true)));
        }

        $product->tags = serialize($tags);
        $product->save();
    }

    /**
     * before generating the download items delete all products that have no author.
     *
     * @param DataContainer $dc
     * @param bool          $deleteOnlyGenerated
     */
    public function cleanGeneratedDownloadItems(ProductModel $product, $deleteOnlyGenerated = false)
    {
        $downloads = $deleteOnlyGenerated ? $this->downloadRegistry->findGeneratedImages($product->id) : $this->downloadRegistry->findBy('pid', $product->pid);

        if (null === $downloads) {
            return;
        }

        while ($downloads->next()) {
            $downloads->delete();
        }
    }

    /**
     * get the upload folder.
     *
     * @param DataContainer $dc
     *
     * @return string
     */
    public function getUploadFolder(DataContainer $dc)
    {
        if (null === ($productArchive = $this->getProductArchive($dc->id))) {
            return Config::get('uploadPath');
        }

        if (null === ($product = $this->productRegistry->findByPk($dc->id))) {
            return Config::get('uploadPath');
        }

        if (null === ($uploadFolder = System::getContainer()->get('huh.utils.file')->getPathFromUuid($productArchive->uploadFolder))) {
            return Config::get('uploadPath');
        }

        if (!isset($product->title)) {
            return $uploadFolder;
        }

        return $uploadFolder.DIRECTORY_SEPARATOR.$product->title;
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
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create ml_product items in ml_product archive ID '.\Contao\Input::get('pid').'.');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(\Contao\Input::get('pid'), $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' ml_product item ID '.$id.' to ml_product archive ID '.\Contao\Input::get('pid').'.');
                }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare('SELECT pid FROM tl_ml_product WHERE id=?')
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid ml_product item ID '.$id.'.');
                }

                if (!in_array($objArchive->pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' ml_product item ID '.$id.' of ml_product archive ID '.$objArchive->pid.'.');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access ml_product archive ID '.$id.'.');
                }

                $objArchive = $database->prepare('SELECT id FROM tl_ml_product WHERE pid=?')
                    ->execute($id);

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
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access ml_product archive ID '.$id.'.');
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

        return '<a href="'.Controller::addToUrl($href).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="'.($row['published'] ? 1 : 0).'"').'</a> ';
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
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish ml_product item ID '.$intId.'.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare('SELECT * FROM tl_ml_product WHERE id=?')
                ->limit(1)
                ->execute($intId);

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
        $database->prepare("UPDATE tl_ml_product SET tstamp=$time, published='".($blnVisible ? '1' : "''")."' WHERE id=?")
            ->execute($intId);

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

    /**
     * add values to tags.
     *
     * @param DataContainer $dc
     * @param string        $field
     * @param array         $tags
     */
    protected function addToTags(ProductModel $product, string $field, array &$tags, DataContainer $dc)
    {
        $fieldValue = $product->{$field};

        if (!is_array(StringUtil::deserialize($fieldValue))) {
            $tags[] = System::getContainer()->get('huh.utils.form')->prepareSpecialValueForOutput($field, $product->{$field}, $dc);

            return;
        }

        $values = [];
        foreach (StringUtil::deserialize($fieldValue) as $value) {
            $values[] = System::getContainer()->get('huh.utils.form')->prepareSpecialValueForOutput($field, $value, $dc);
        }

        $tags = array_unique(array_merge($tags, $values));
    }

    /**
     * create download items.
     *
     * @param DataContainer $dc
     *
     * @throws \Exception
     */
    protected function createDownloadItems(ProductModel $product)
    {
        foreach (StringUtil::deserialize($product->uploadedFiles, true) as $file) {
            $path = System::getContainer()->getParameter('kernel.project_dir').DIRECTORY_SEPARATOR.
                    System::getContainer()->get('huh.utils.file')->getPathFromUuid($file);

            $extension = System::getContainer()->get('huh.utils.file')->getFileExtension($path);

            if (in_array($extension, explode(',', Config::get('validImageTypes')), true)) {
                $this->createImageDownloadItems($path, $extension, $product);
            }
        }
    }

    /**
     * Create image download items that will be resized.
     *
     * @param string        $path
     * @param string        $extension
     * @param DataContainer $dc
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function createImageDownloadItems(string $path, string $extension, ProductModel $product)
    {
        if (null === ($productArchive = $this->getProductArchive($product->id))) {
            return false;
        }

        $sizes = StringUtil::deserialize(System::getContainer()->get('huh.utils.dca')->getOverridableProperty('imageSizes', [
            $productArchive, $product,
        ]), true);

        $extension = '.'.$extension;

        foreach ($sizes as $size) {
            if (null === ($sizeModel = $this->framework->getAdapter(ImageSizeModel::class)->findByPk($size))) {
                continue;
            }

            if (!$this->isResizable($path, $sizeModel)) {
                continue;
            }

            $targetFile = str_replace($extension, '_'.$sizeModel->name.$extension, $path);
            $resizeImage = System::getContainer()->get('contao.image.image_factory')->create($path, $size, $targetFile);

            $this->createDownloadItem($resizeImage->getPath(), $product, $sizeModel);
        }
    }

    /**
     * check if the size of the image is bigger than the resize measures.
     *
     * @param string         $image
     * @param ImageSizeModel $size
     *
     * @return bool
     */
    protected function isResizable(string $image, ImageSizeModel $size)
    {
        $imageSize = getimagesize($image);

        if ($size->width > $imageSize[0] && $size->height > $imageSize[1]) {
            return false;
        }

        return true;
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
    protected function createDownloadItem(string $path, ProductModel $product, ImageSizeModel $size = null)
    {
        $downloadItem = new DownloadModel();
        $title = $product->title;

        $path = str_replace(System::getContainer()->get('huh.utils.container')->getProjectDir().DIRECTORY_SEPARATOR, '', $path);

        if (null === ($downloadFile = FilesModel::findByPath($path))) {
            $downloadFile = Dbafs::addResource(urldecode($path));
        }

        if (null !== $size) {
            $title .= '_'.$size->name;
        }

        $downloadItem->tstamp = $downloadItem->dateAdded = time();

        $downloadItem->title = $title;
        $downloadItem->pid = $product->id;
        $downloadItem->downloadFile = $downloadFile->uuid;

        $downloadItem->published = true;

        $downloadItem->save();
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
}
