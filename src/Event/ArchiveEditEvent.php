<?php

namespace HeimrichHannot\MediaLibraryBundle\Event;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;

readonly class ArchiveEditEvent
{
    public function __construct(
        public string        $archiveType,
        private ArchiveModel $archiveModel,
    ) {}

    public function getArchiveModel(): ArchiveModel
    {
        return $this->archiveModel;
    }
}