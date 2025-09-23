<?php

namespace HeimrichHannot\MediaLibraryBundle\ArchiveType;

class VideoArchiveType extends AbstractArchiveType
{
    public static function getAlias(): string
    {
        return 'video';
    }
}