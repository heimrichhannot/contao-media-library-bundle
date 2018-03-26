<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\WatchlistBundle\Manager\AjaxManager;
use HeimrichHannot\WatchlistBundle\Manager\WatchlistItemManager;

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

        $template = new FrontendTemplate('watchlist_add_action');
        $template->added = false;
        $template->uuid = '';
        $template->options = '';

        if (null === ($downloadFiles = System::getContainer()->get('huh.media_library.download_registry')->findByPid($this->_raw['id']))) {
            $template->uuid = StringUtil::uuidToBin($this->_raw['downloadFiles']);
        } else {
            $template->options = $this->getOptions($downloadFiles);
        }

        if (empty($downloadFiles)) {
            return '';
        }

        $template->id = $this->_raw['id'];
        $template->moduleId = $listConfig->watchlist_config;
        $template->type = WatchlistItemManager::WATCHLIST_ITEM_TYPE_FILE;
        $template->action =
            System::getContainer()->get('huh.ajax.action')->generateUrl(AjaxManager::XHR_GROUP, AjaxManager::XHR_WATCHLIST_ADD_ACTION);
        $template->title = sprintf($GLOBALS['TL_LANG']['WATCHLIST']['addTitle'], $data['title']);
        $template->link = $GLOBALS['TL_LANG']['WATCHLIST']['addLink'];

        return $template->parse();
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
}
