<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ArchiveContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCallback(table: ArchiveContainer::TABLE, target: 'fields.type.options', priority: 100)]
#[AsCallback(table: ItemContainer::TABLE, target: 'fields.type.options', priority: 100)]
readonly class ArchiveTypeOptionsListener
{
    public function __construct(
        private ArchiveTypeCollection $archiveTypes,
        private TranslatorInterface   $translator,
    ) {}

    public function __invoke(): array
    {
        $aliases = $this->archiveTypes->aliases();
        $options = [];

        foreach ($aliases as $alias) {
            $options[$alias] = $this->translator->trans('archive_type.' . $alias, [], 'huh_media_library');
        }

        return $options;
    }
}