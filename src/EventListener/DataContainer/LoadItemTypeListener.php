<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsCallback(table: ItemContainer::TABLE, target: 'config.onload')]
readonly class LoadItemTypeListener
{
    public function __construct(
        private RequestStack $requestStack,
    ) {}

    public function __invoke(?DataContainer $dc = null): void
    {
        if (!$dc || !$dc->id || $this->requestStack->getCurrentRequest()?->query?->get('act') !== 'edit') {
            return;
        }

        if (!$item = ItemModel::findByPk($dc->id)) {
            return;
        }

        if (!$archive = $item->getRelated('pid')) {
            return;
        }

        if (!$archive->type) {
            return;
        }

        $item->type = $archive->type;
    }
}