<?php

namespace HeimrichHannot\MediaLibraryBundle\ArchiveType;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;

class ImageArchiveType extends AbstractArchiveType
{
    public static function getAlias(): string
    {
        return 'image';
    }

    public function getArchivePalette(ArchiveModel $archive): string
    {
        return '{image_legend},imageSizes';
    }

    public function supportsImageSizeDownloads(ArchiveModel $archive): bool
    {
        return \count($archive->getImageSizes());
    }
}