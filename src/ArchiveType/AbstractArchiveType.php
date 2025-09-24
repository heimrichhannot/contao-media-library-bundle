<?php

namespace HeimrichHannot\MediaLibraryBundle\ArchiveType;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('huh.media_library.archive_type')]
abstract class AbstractArchiveType
{
    abstract public static function getAlias(): string;

    public function getItemPalette(ArchiveModel $archive, ItemModel $item): string
    {
        return '';
    }

    public function getArchivePalette(ArchiveModel $archive): string
    {
        return '';
    }
}