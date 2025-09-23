<?php

namespace HeimrichHannot\MediaLibraryBundle\ArchiveType;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

class FileArchiveType extends AbstractArchiveType
{
    public static function getAlias(): string
    {
        return 'file';
    }

    public function getItemPalette(ArchiveModel $archive, ItemModel $item): string
    {
        // TODO: Implement getItemPalette() method.
    }
}