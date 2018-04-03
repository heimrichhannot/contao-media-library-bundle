<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Model\WatchlistItemModel;

class MediaLibraryListItem extends \HeimrichHannot\ListBundle\Item\DefaultItem
{
    /**
     * add the add-to-watchlist button to the template.
     *
     * @return string
     */
    public function getAddToWatchlistButton()
    {
        $module = $this->getModule();

        $listConfig = System::getContainer()->get('huh.list.list-config-registry')->findByPk($module['listConfig']);

        if (null !== ($watchlistConfig =
                System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_module', $listConfig->watchlist_config))
            && !System::getContainer()->get('huh.watchlist.watchlist_manager')->checkPermission($watchlistConfig)) {
            return;
        }

        $template = new FrontendTemplate('watchlist_add_action');
        $template->added = false;
        $template->uuid = '';
        $template->options = '';

        if (null === ($downloadFiles = System::getContainer()->get('huh.media_library.download_registry')->findByPid($this->_raw['id']))) {
            $template->uuid = json_encode([
                'title' => $this->_raw['title'],
                'uuid' => StringUtil::binToUuid(deserialize($this->_raw['uploadedFiles'])[0]),
            ]);
        }

        if (null !== $downloadFiles && 1 === count($downloadFiles)) {
            $template->uuid = json_encode([
                'title' => $downloadFiles->title,
                'uuid' => StringUtil::binToUuid($downloadFiles->downloadFile),
            ]);
        }

        if (null !== $downloadFiles && count($downloadFiles) > 1) {
            $template->options = $this->getOptions($downloadFiles);
        }

        $template->id = $this->_raw['id'];
        $template->moduleId = $listConfig->watchlist_config;
        $template->type = WatchlistItemModel::WATCHLIST_ITEM_TYPE_FILE;
        $template->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);
        $template->title = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['addTitle'], $this->_raw['title']);
        $template->link = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];

        return $template->parse();
    }

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
                return [System::getContainer()->get('huh.utils.file')->getPathFromUuid($downloads->downloadFile), false];
            }

            return [$this->getOptions($downloads), true];
        }

        if (empty($downloads = StringUtil::deserialize($this->_raw['uploadedFiles'], true))) {
            return [];
        }

        if (1 === count($downloads)) {
            return [System::getContainer()->get('huh.utils.file')->getPathFromUuid($this->_raw['uploadedFiles']), false];
        }

        return [$this->getOptionsFromArray($downloads), true];
    }

    protected function getOptions($downloadItems)
    {
        $options = [];
        foreach ($downloadItems as $downloadItem) {
            $options[] = [
                'title' => $downloadItem->title,
                'uuid' => StringUtil::binToUuid($downloadItem->downloadFile),
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
