<?php

namespace HeimrichHannot\MediaLibraryBundle\Event;

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Util\Str;
use Symfony\Contracts\EventDispatcher\Event;

class ArchivePaletteEvent extends Event
{
    public function __construct(
        public readonly string       $archiveType,
        public readonly ArchiveModel $archiveModel,
        public string                $palette,
        public string                $prefix,
        public string                $suffix,
    ) {}

    public function assemblePalette(): string
    {
        return Str::mergePalettes($this->prefix, $this->palette, $this->suffix);
    }
}