<?php

namespace HeimrichHannot\MediaLibraryBundle\Collection;

use HeimrichHannot\MediaLibraryBundle\ArchiveType\AbstractArchiveType;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class ArchiveTypeCollection
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
            $this->archiveTypes = [];

            foreach ($this->archiveTypesIterable as $alias => $service)
            {
                $this->archiveTypes[$alias] = $service;
            }
        }

        return $this->archiveTypes;
    }

    public function all(): iterable
    {
        return $this->resolve();
    }

    public function get(string $alias): ?AbstractArchiveType
    {
        if (!$alias) {
            return null;
        }

        return $this->resolve()[$alias] ?? null;
    }

    public function aliases(): array
    {
        return \array_keys($this->resolve());
    }
}