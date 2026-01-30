<?php

namespace HeimrichHannot\MediaLibraryBundle\Flare\ListType;

use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListType;
use HeimrichHannot\FlareBundle\Enum\SqlEquationOperator;
use HeimrichHannot\FlareBundle\Event\ListQueryPrepareEvent;
use HeimrichHannot\FlareBundle\Event\ListSpecificationCreatedEvent;
use HeimrichHannot\FlareBundle\FilterElement\PublishedElement;
use HeimrichHannot\FlareBundle\FilterElement\SimpleEquationElement;
use HeimrichHannot\FlareBundle\ListType\AbstractListType;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsListType(self::TYPE, dataContainer: ItemContainer::TABLE, palette: '{archive_legend},ml_archive')]
class MediaLibraryArchiveListType extends AbstractListType implements MediaLibraryFilesListTypeInterface
{
    public const TYPE = 'ml_archive';
    public const ALIAS_ARCHIVE = 'ml_archive';

    public function onListQueryPrepareEvent(ListQueryPrepareEvent $event): void
    {
        $builder = $event->getListQueryBuilder();

        $builder->innerJoin(
            table: 'tl_ml_archive',
            as: self::ALIAS_ARCHIVE,
            on: $builder->makeJoinOn(self::ALIAS_ARCHIVE, 'id', 'pid')
        );
    }

    #[AsEventListener(priority: 200)]
    public function onListSpecificationCreated(ListSpecificationCreatedEvent $config): void
    {
        if ($config->listSpecification->type !== self::TYPE) {
            return;
        }

        $filters = $config->listSpecification->getFilters();

        if ($archiveId = $config->listSpecification->ml_archive) {
            $filters->set(
                '_ml_archive_id',
                SimpleEquationElement::define('pid', SqlEquationOperator::EQUALS, $archiveId)
            );
        }

        if (!$filters->hasType(PublishedElement::TYPE)) {
            $filters->add(PublishedElement::define());
        }
    }
}