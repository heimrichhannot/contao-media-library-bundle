<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
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
                'file' => Environment::get('uri').'?file='.$file,
                'uuid' => $downloadItem->file,
                'data' => $downloadItem->row(),
            ];
        }

        return $options;
    }
}
