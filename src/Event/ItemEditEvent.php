<?php

namespace HeimrichHannot\MediaLibraryBundle\Event;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

readonly class ItemEditEvent
{
    public function __construct(
        public string        $archiveType,
        private ArchiveModel $archiveModel,
        private ItemModel    $itemModel,
    ) {}

    public function getArchiveModel(): ArchiveModel
    {
        return $this->archiveModel;
    }

    public function getItemModel(): ItemModel
    {
        return $this->itemModel;
    }
}