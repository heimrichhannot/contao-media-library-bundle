<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer;

use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\Event\ArchivePaletteEvent;
use HeimrichHannot\MediaLibraryBundle\Util\Str;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(priority: 160)]
readonly class ImageSizePaletteListener
{
    public function __construct(
        private ArchiveTypeCollection $types,
    ) {}

    public function __invoke(ArchivePaletteEvent $event): void
    {
        if (!$type = $this->types->get($event->archiveType)) {
            return;
        }

        if ($type->supportsImageSizeDownloads($event->archiveModel)) {
            $event->suffix = Str::mergePalettes('{image_legend},imageSizes', $event->suffix);
        }
    }
}