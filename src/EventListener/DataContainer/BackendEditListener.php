<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ArchiveContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use HeimrichHannot\MediaLibraryBundle\Event\ArchiveEditEvent;
use HeimrichHannot\MediaLibraryBundle\Event\ItemEditEvent;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class BackendEditListener
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private RequestStack             $requestStack,
    ) {}

    #[AsCallback(table: ArchiveContainer::TABLE, target: 'config.onload')]
    public function onloadEditArchive(?DataContainer $dc): void
    {
        if (!$dc || !$dc->id) {
            return;
        }

        if ($this->requestStack->getCurrentRequest()?->query?->get('act') !== 'edit') {
            return;
        }

        if (!$archive = ArchiveModel::findByPk($dc->id)) {
            return;
        }

        $this->eventDispatcher->dispatch(new ArchiveEditEvent(
            archiveType: $archive->type,
            archiveModel: $archive,
        ));
    }

    #[AsCallback(table: ItemContainer::TABLE, target: 'config.onload')]
    public function onloadEditItem(?DataContainer $dc): void
    {
        if (!$dc || !$dc->id) {
            return;
        }

        if ($this->requestStack->getCurrentRequest()?->query?->get('act') !== 'edit') {
            return;
        }

        if (!$item = ItemModel::findByPk($dc->id)) {
            return;
        }

        $archive = $item->getRelated('pid');
        if (!$archive instanceof ArchiveModel) {
            return;
        }

        $this->eventDispatcher->dispatch(new ItemEditEvent(
            archiveType: $archive->type,
            archiveModel: $archive,
            itemModel: $item,
        ));
    }
}