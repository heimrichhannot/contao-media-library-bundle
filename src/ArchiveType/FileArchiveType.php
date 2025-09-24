<?php

namespace HeimrichHannot\MediaLibraryBundle\ArchiveType;

class FileArchiveType extends AbstractArchiveType
{
    public static function getAlias(): string
    {
        return 'file';
    }
}