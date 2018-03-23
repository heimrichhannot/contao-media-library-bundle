<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Backend;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\Dbafs;
use Contao\FilesModel;
use Contao\ImageSizeModel;
use Contao\System;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Registry\DownloadRegistry;
use HeimrichHannot\MediaLibraryBundle\Registry\ProductArchiveRegistry;
use HeimrichHannot\MediaLibraryBundle\Registry\ProductRegistry;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class Product
{
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

    public function modifyPalette(DataContainer $dc)
    {
        if (null === ($productArchive = $this->getProductArchive($dc->id))) {
            return;
        }

        $paletteConfig = deserialize($productArchive->palette, true);

        if (empty($paletteConfig)) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA']['tl_ml_product'];

        $dca['palettes']['default'] = implode(',', $paletteConfig);
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
        if ($dc->activeRecord->doNotCreateDownloadItems) {
            return;
        }

        $files = deserialize($dc->activeRecord->uploadedFiles, true);

        if (empty($files)) {
            return;
        }

        $this->cleanGeneratedDownloadItems($dc, true);

        $this->createDownloadItems($dc);
    }

    /**
     * generate tags from values of product.
     *
     * @param DataContainer $dc
     *
     * @return bool
     */
    public function generateTags(DataContainer &$dc)
    {
        if (null === ($productArchive = $this->getProductArchive($dc->id))) {
            return false;
        }

        if (null === ($product = $this->productRegistry->findByPk($dc->id))) {
            return false;
        }

        $fieldsForTags = deserialize($productArchive->fieldsForTags, true);

        // use all fields if none is defined
        if (empty($fieldsForTags)) {
            $fieldsForTags = System::getContainer()
                ->get('huh.utils.dca')
                ->getFields('tl_ml_product',
                    ['inputTypes' => ['text', 'textarea', 'select', 'multifileupload', 'checkbox', 'tagsinput'], 'localizeLabels' => false]);
        }

        $tags = [];

        foreach ($fieldsForTags as $field) {
            $this->addToTag($dc, $field, $tags);
        }

        if (isset($dc->activeRecord->tag) && !in_array('tag', $fieldsForTags, true)) {
            $tags = array_unique(array_merge($tags, deserialize($dc->activeRecord->tag, true)));
        }

        $product->tag = serialize($tags);
        $product->save();
    }

    /**
     * before generating the download items delete all products that have no author.
     *
     * @param DataContainer $dc
     * @param bool          $deleteOnlyGenerated
     */
    public function cleanGeneratedDownloadItems(DataContainer $dc, $deleteOnlyGenerated = false)
    {
        $downloads = $deleteOnlyGenerated
            ? $this->downloadRegistry->findGeneratedImages($dc->activeRecord->id) : $this->downloadRegistry->findBy('pid', $dc->activeRecord->pid);

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

        if (null === ($uploadFolder = System::getContainer()->get('huh.utils.file')->getPathFromUuid($productArchive->uploadFolder))) {
            return Config::get('uploadPath');
        }

        if (!isset($dc->activeRecord->title)) {
            return $uploadFolder;
        }

        return $uploadFolder.DIRECTORY_SEPARATOR.$dc->activeRecord->title;
    }

    /**
     * add values to tags.
     *
     * @param DataContainer $dc
     * @param string        $field
     * @param array         $tags
     */
    protected function addToTag(DataContainer $dc, string $field, array &$tags)
    {
        $fieldValue = $dc->activeRecord->{$field};

        if (!is_array(deserialize($fieldValue))) {
            $tags[] = System::getContainer()->get('huh.utils.form')->prepareSpecialValueForOutput($field, $dc->activeRecord->{$field}, $dc);

            return;
        }

        $values = [];
        foreach (deserialize($fieldValue) as $value) {
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
    protected function createDownloadItems(DataContainer $dc)
    {
        foreach (deserialize($dc->activeRecord->uploadedFiles, true) as $file) {
            $path = TL_ROOT.DIRECTORY_SEPARATOR.System::getContainer()->get('huh.utils.file')->getPathFromUuid($file);
            $extension = System::getContainer()->get('huh.utils.file')->getFileExtension($path);

            if (in_array($extension, explode(',', Config::get('validImageTypes')), true)) {
                $this->createImageDownloadItems($path, $extension, $dc);
            }

            $this->createDownloadItem($path, $dc);
        }
    }

    /**
     * create image dowload items that will be resized.
     *
     * @param string        $path
     * @param string        $extension
     * @param DataContainer $dc
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function createImageDownloadItems(string $path, string $extension, DataContainer $dc)
    {
        if (null === ($productArchive = $this->getProductArchive($dc->id))) {
            return false;
        }

        $sizes =
            $dc->activeRecord->overrideImageSize ? deserialize($dc->activeRecord->imageSize, true) : deserialize($productArchive->imageSize, true);
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

            $this->createDownloadItem($resizeImage->getPath(), $dc, $sizeModel);
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
    protected function createDownloadItem(string $path, DataContainer $dc, ImageSizeModel $size = null)
    {
        $downloadItem = new DownloadModel();
        $title = $dc->activeRecord->title;

        $path = str_replace(TL_ROOT.DIRECTORY_SEPARATOR, '', $path);

        if (null === ($downloadFile = FilesModel::findByPath($path))) {
            $downloadFile = Dbafs::addResource(urldecode($path));
        }

        if (null !== $size) {
            $title .= '_'.$size->name;
        }

        $downloadItem->tstamp = time();
        $downloadItem->dateAdded = time();

        $downloadItem->title = $title;
        $downloadItem->pid = $dc->activeRecord->id;
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
