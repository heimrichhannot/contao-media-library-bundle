<?php

namespace HeimrichHannot\MediaLibraryBundle\Event;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use Symfony\Contracts\EventDispatcher\Event;

class ArchivePaletteEvent extends Event
{
    public const NAME_TEMPLATE = 'huh.media_library.archive_type.%s.archive_palette';

    public static function getEventName(string $archiveType): string
    {
        return \sprintf(self::NAME_TEMPLATE, $archiveType);
    }

    public function __construct(
        public readonly ArchiveModel $archiveModel,
        public string                $palette,
        public string                $prefix,
        public string                $suffix,
    ) {}
}