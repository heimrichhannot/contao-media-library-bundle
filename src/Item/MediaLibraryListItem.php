<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;

class MediaLibraryListItem extends \HeimrichHannot\ListBundle\Item\DefaultItem
{
    public function getDownloadLink()
    {
        if (ProductModel::ITEM_LICENCE_TYPE_LOCKED === $this->_raw['licence']) {
            return;
        }

        list($downloads, $hasOptions) = $this->getDownloadItems();

        if (empty($downloads)) {
            return;
        }

        System::loadLanguageFile('tl_ml_product');

        $template = new FrontendTemplate('download_action');

        $template->link = $GLOBALS['TL_LANG']['tl_ml_product']['downloadLink'];
        $template->title = sprintf($GLOBALS['TL_LANG']['tl_ml_product']['downloadLink'], $this->_raw['title']);

        if (!$hasOptions) {
            $template->file = '?file='.$downloads;

            return $template->parse();
        }

        $template->options = $downloads;
        $template->action = System::getContainer()->get('huh.ajax.action')->generateUrl(\HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_XHR_GROUP, \HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS);

        return $template->parse();
    }

    protected function getDownloadItems()
    {
        if (null !== ($downloads = System::getContainer()->get('huh.media_library.download_registry')->findByPid($this->_raw['id']))) {
            if (1 === count($downloads)) {
                return [System::getContainer()->get('huh.utils.file')->getPathFromUuid($downloads->file), false];
            }

            return [$this->getOptions($downloads), true];
        }

        $downloads = StringUtil::deserialize($this->_raw['uploadedFiles'], true);

        if (empty($downloads)) {
            return [];
        }

        if (1 === count($downloads)) {
            return [System::getContainer()->get('huh.utils.file')->getPathFromUuid($downloads[0]), false];
        }

        return [$this->getOptionsFromArray($downloads), true];
    }

    protected function getOptions($downloadItems)
    {
        $options = [];
        foreach ($downloadItems as $downloadItem) {
            $options[] = [
                'title' => $downloadItem->title,
                'uuid' => StringUtil::binToUuid($downloadItem->file),
            ];
        }

        return json_encode($options);
    }

    protected function getOptionsFromArray($downloadItems)
    {
        $options = [];
        foreach ($downloadItems as $item) {
            $options[] = [
                'title' => $this->_raw['title'],
                'uuid' => $item,
            ];
        }

        return $options;
    }

    protected function checkPermission($config)
    {
        if (!$config->protected) {
            return true;
        }

        if (null === ($user = FrontendUser::getInstance())) {
            return false;
        }

        if (!array_intersect(StringUtil::deserialize($config->groups, true), StringUtil::deserialize($user->groups, true))) {
            return false;
        }

        return true;
    }
}
