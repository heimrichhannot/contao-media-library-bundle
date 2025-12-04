<?php

namespace HeimrichHannot\MediaLibraryBundle\Event;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Symfony\Contracts\EventDispatcher\Event;

class ItemEditEvent extends Event
{
    public function __construct(
        public readonly string       $archiveType,
        public readonly ArchiveModel $archiveModel,
        public readonly ItemModel    $itemModel,
    ) {}
}