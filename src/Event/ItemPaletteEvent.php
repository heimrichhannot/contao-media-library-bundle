<?php

namespace HeimrichHannot\MediaLibraryBundle\Event;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Symfony\Contracts\EventDispatcher\Event;

class ItemPaletteEvent extends Event
{
    public const NAME_TEMPLATE = 'huh.media_library.archive_type.%s.item_palette';

    public static function getEventName(string $archiveType): string
    {
        return \sprintf(self::NAME_TEMPLATE, $archiveType);
    }

    public function __construct(
        public readonly ArchiveModel $archiveModel,
        public readonly ItemModel    $itemModel,
        public string                $palette,
        public string                $prefix,
        public string                $suffix,
    ) {}
}