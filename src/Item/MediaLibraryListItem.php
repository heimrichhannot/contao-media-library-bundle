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
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\FileCredit\Validator;
use HeimrichHannot\ListBundle\Item\DefaultItem;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;

class MediaLibraryListItem extends DefaultItem
{
    public function getDownloadLink()
    {
        if (ProductModel::ITEM_LICENCE_TYPE_LOCKED === $this->getRawValue('licence')) {
            return;
        }

        if (!$this->checkPermission()) {
            return;
        }

        list($downloads, $hasOptions) = $this->getDownloadItems();

        if (empty($downloads)) {
            return;
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
                $uuid = Validator::isUuid($downloads->file) ? $downloads->file : reset(StringUtil::deserialize($downloads->file));


                $items[] = [
                    'label' => htmlentities($download->title),
                    'file' => System::getContainer()->get('huh.utils.file')->getPathFromUuid($uuid, false),
                    'uuid' => StringUtil::binToUuid($uuid)
                ];
            }

            if(1 == count($items)) {
                return [$items[0]['file'],false];
            }

            return [json_encode($items), true];
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

    protected function checkPermission()
    {
        if (null === ($archive = System::getContainer()->get('huh.media_library.product_archive_registry')->findByPk($this->getRawValue('pid')))) {
            return false;
        }

        if (!$archive->protected) {
            return true;
        }

        if (null === ($user = FrontendUser::getInstance())) {
            return false;
        }

        if (!array_intersect(StringUtil::deserialize($archive->groups, true), StringUtil::deserialize($user->groups, true))) {
            return false;
        }

        return true;
    }
}
