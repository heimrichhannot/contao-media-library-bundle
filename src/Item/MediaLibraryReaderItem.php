<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;

class MediaLibraryReaderItem extends DefaultItem
{
    const WATCHLIST_MODULE_ID = 9;

    public function getDownloadItems()
    {
        $options = [];

        if (null === ($downloadItems = System::getContainer()->get('huh.media_library.download_registry')->findByPid($this->_raw['id']))) {
            return $options;
        }

        foreach ($downloadItems as $downloadItem) {
            if (null === ($file = System::getContainer()->get('huh.utils.file')->getPathFromUuid($downloadItem->file))) {
                continue;
            }

            $options[] = [
                'label' => $downloadItem->title,
                'file' => '?file='.$file,
            ];
        }

        return $options;
    }

    /**
     * get additional data from fields set in product archive.
     *
     * @return array|void
     */
    public function getAdditionalData()
    {
        if (null === ($archive = System::getContainer()->get('huh.media_library.product_archive_registry')->findByPk($this->_raw['pid']))) {
            return;
        }

        if (empty($additionalFields = StringUtil::deserialize($archive->additionalFields, true))) {
            return;
        }

        $additionalData = [];
        foreach ($additionalFields as $field) {
            if ('' == ($value = System::getContainer()->get('huh.utils.form')->prepareSpecialValueForOutput($field, $this->_raw[$field], $this->dc))) {
                continue;
            }

            $additionalData[$field] = [
                'label' => $GLOBALS['TL_LANG'][$this->getDataContainer()][$field][0],
                'value' => $value,
            ];
        }

        return $additionalData;
    }

    /**
     * @return string|void
     */
    public function getCopyright()
    {
        if (empty($value = StringUtil::deserialize($this->_raw['copyright'], true))) {
            return;
        }

        return $value;
    }
}
