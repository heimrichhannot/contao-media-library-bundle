<?php

namespace HeimrichHannot\MediaLibraryBundle\ArchiveType;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

class VideoArchiveType extends AbstractArchiveType
{
    public static function getAlias(): string
    {
        return 'video';
    }

    public function getItemPalette(ArchiveModel $archive, ItemModel $item): string
    {
        return '{video_legend},videoPosterImage;';
    }
}