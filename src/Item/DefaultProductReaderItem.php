<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\Controller;
use Contao\Environment;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\ReaderBundle\Item\DefaultItem;

class DefaultProductReaderItem extends DefaultItem
{
    public function getDownloadItems()
    {
        $options = [];

        if (System::getContainer()->get('huh.request')->getGet('file')) {
            Controller::sendFileToBrowser(System::getContainer()->get('huh.request')->getGet('file'));
        }

        if (null === ($downloads = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_ml_download', ['tl_ml_download.pid=?'], [$this->_raw['id']], [
            'order' => 'imageSize ASC',
            ]))) {
            return $options;
        }

        foreach ($downloads as $downloadItem) {
            if (null === ($file = System::getContainer()->get('huh.utils.file')->getPathFromUuid($downloadItem->file))) {
                continue;
            }

            $options[] = [
                'label' => html_entity_decode($downloadItem->title),
                'file' => System::getContainer()->get('huh.utils.url')->addQueryString('file='.$file, Environment::get('uri')),
                'uuid' => $downloadItem->file,
                'data' => $downloadItem->row(),
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
        if (null === ($archive = $this->getProductArchive())) {
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

    public function getProductArchive(): ?Model
    {
        return System::getContainer()->get('huh.utils.model')->findModelInstanceByPk(
            'tl_ml_product_archive', ['tl_ml_product_archive.pid=?'], [$this->_raw['pid']]);
    }
}
