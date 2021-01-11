<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\Environment;
use Contao\System;
use HeimrichHannot\ListBundle\Item\DefaultItem;

class DefaultDownloadListItem extends DefaultItem
{
    public function getSizedDownloads()
    {
        $options = [];

        if (null === ($downloadItems = System::getContainer()->get('huh.utils.model')->findModelInstancesBy(
            'tl_ml_download', ['tl_ml_download.pid=?', 'tl_ml_download.originalDownload=?'], [$this->getRawValue('pid'), $this->getRawValue('id')], [
                'order' => 'tl_ml_download.title ASC',
            ]))) {
            return [];
        }

        foreach ($downloadItems as $downloadItem) {
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

    public function getOriginalDownload()
    {
        if (null === ($uuid = $this->getRawValue('file'))) {
            return [];
        }

        if (null === ($file = System::getContainer()->get('huh.utils.file')->getPathFromUuid($uuid))) {
            return [];
        }

        return [
            'label' => html_entity_decode($this->getRawValue('title')),
            'file' => System::getContainer()->get('huh.utils.url')->addQueryString('file='.$file, Environment::get('uri')),
            'uuid' => $this->getRawValue('file'),
            'data' => $this->getRaw(),
        ];
    }

    public function getDownloadItems()
    {
        $original = $this->getOriginalDownload();

        if (!$original) {
            return $this->getSizedDownloads();
        }

        return array_merge([$original], $this->getSizedDownloads());
    }
}
