<?php

namespace HeimrichHannot\MediaLibraryBundle\Collection;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ArchiveCollection
{
    private array $archiveTypes;

    public function __construct(
        #[TaggedIterator('huh.media_library.archive_type', defaultIndexMethod: 'getAlias')]
        private readonly iterable $archiveTypesIterable,
    ) {}

    private function resolve(): array
    {
        if (!isset($this->archiveTypes))
        {
            foreach ($this->archiveTypesIterable as $alias => $service)
            {
                $this->archiveTypes[$alias] = $service;
            }
        }

        return $this->archiveTypes;
    }

    public function getAll(): iterable
    {
        return $this->resolve();
    }

    public function get(string $alias): ?object
    {
        return $this->resolve()[$alias] ?? null;
    }
}