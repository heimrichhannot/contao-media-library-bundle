<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\Controller;
use Contao\Environment;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;

class MediaLibraryReaderItem extends DefaultItem
{
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
                'label' => htmlentities($downloadItem->title),
                'file' => Environment::get('url') . '?file=' . $file,
                'uuid' => $downloadItem->file
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

    /**
     * @return Model|null
     */
    public function getProductArchive(): ?Model
    {
        return $archive = System::getContainer()->get('huh.media_library.product_archive_registry')->findByPk($this->getRawValue('pid'));
    }
}
