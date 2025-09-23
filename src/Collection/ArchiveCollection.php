<?php

namespace HeimrichHannot\MediaLibraryBundle\Collection;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

readonly class ArchiveCollection
{
    public function __construct(
        #[TaggedIterator('huh.media_library.archive_type', defaultIndexMethod: 'getAlias')]
        private iterable $archiveTypes,
    ) {}

    public function getAll(): iterable
    {
        return $this->archiveTypes;
    }

    public function get(string $alias): ?object
    {
        return $this->archiveTypes[$alias] ?? null;
    }
}