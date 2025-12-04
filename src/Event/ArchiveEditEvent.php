<?php

namespace HeimrichHannot\MediaLibraryBundle\Event;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use Symfony\Contracts\EventDispatcher\Event;

class ArchiveEditEvent extends Event
{
    public function __construct(
        public readonly string       $archiveType,
        public readonly ArchiveModel $archiveModel,
    ) {}
}