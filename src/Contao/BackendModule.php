<?php

namespace HeimrichHannot\MediaLibraryBundle\Contao;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

class BackendModule
{
    public const CATEGORY = 'content';
    public const NAME = 'media_library';

    public static function getTables(): array
    {
        return [
            ArchiveModel::getTable(),
            DownloadModel::getTable(),
            ItemModel::getTable(),
        ];
    }
}