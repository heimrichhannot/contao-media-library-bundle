<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FileCredit\Validator;
use HeimrichHannot\ListBundle\Item\DefaultItem;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;

class MediaLibraryListItem extends DefaultItem
{
    public function getDownloadLink()
    {
        if (!$this->checkPermission()) {
            return null;
        }

        list($downloads, $hasOptions) = $this->getDownloadItems();

        if (empty($downloads)) {
            return null;
        }

        System::loadLanguageFile('tl_ml_product');

        $template = new FrontendTemplate('download_action');

        $template->link = $GLOBALS['TL_LANG']['tl_ml_product']['downloadLink'];
        $template->title = sprintf($GLOBALS['TL_LANG']['tl_ml_product']['downloadLink'], $this->getRawValue('title'));

        if (!$hasOptions) {
            $template->file = $downloads;

            return $template->parse();
        }

        $template->options = $downloads;
        $template->action = System::getContainer()->get('huh.ajax.action')->generateUrl(\HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_XHR_GROUP, \HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS);

        return $template->parse();
    }

    protected function getDownloadItems(string $fileField = 'uploadedFiles')
    {
        if (null !== ($downloads = System::getContainer()->get('huh.media_library.download_registry')->findByPid($this->getRawValue('id')))) {
            $items = [];

            foreach($downloads as $download) {
                $uuid = Validator::isUuid($download->file) ? $download->file : reset(StringUtil::deserialize($download->file));


                $items[] = [
                    'label' => urlencode($download->title),
                    'file' => System::getContainer()->get('huh.utils.file')->getPathFromUuid($uuid, false),
                    'uuid' => StringUtil::binToUuid($uuid)
                ];
            }

            if(1 == count($items)) {
                return [$items[0]['file'],false];
            }

            return [json_encode($items, JSON_UNESCAPED_SLASHES), true];
        }

        $downloads = StringUtil::deserialize($this->getRawValue($fileField), true);

        if (empty($downloads)) {
            return [];
        }

        if (1 === \count($downloads)) {
            return [System::getContainer()->get('huh.utils.file')->getPathFromUuid($downloads[0]), false];
        }

        return [$this->getOptionsFromArray($downloads), true];
    }


    protected function getOptionsFromArray($downloadItems)
    {
        $options = [];
        foreach ($downloadItems as $item) {
            $options[] = [
                'title' => $this->getRawValue('title'),
                'uuid' => $item,
            ];
        }

        return $options;
    }

    /**
     * @return bool
     */
    protected function checkPermission(): bool
    {
        if (null === ($archive = $this->getProductArchive())) {
            return false;
        }

        $permitted = true;

        if($archive->protected) {
            $permitted = $this->checkUserPermission($archive);
        }

        if($archive->preventLockedProductsFromDownload) {
            $permitted = false;
        }

        return $permitted;
    }

    /**
     * @param ProductArchiveModel $archive
     * @return bool
     */
    protected function checkUserPermission(ProductArchiveModel $archive): bool
    {
        if (null === ($user = FrontendUser::getInstance())) {
            return false;
        }

        if (!array_intersect(StringUtil::deserialize($archive->groups, true), StringUtil::deserialize($user->groups, true))) {
            return false;
        }

        return true;
    }


    /**
     * @return Model|null
     */
    public function getProductArchive(): ?Model
    {
        return $archive = System::getContainer()->get('huh.media_library.product_archive_registry')->findByPk($this->getRawValue('pid'));
    }

    /**
     * @return bool
     */
    public function getLocked(): bool
    {
        return ProductModel::ITEM_LICENCE_TYPE_LOCKED == $this->getRawValue('licence');
    }

    /**
     * @return string|null
     */
    public function getLockedText(): ?string
    {
        $archive = $this->getProductArchive();

        return System::getContainer()->get('translator')->trans($archive->lockedProductText ? : 'huh.mediaLibrary.locked.default');
    }

}
