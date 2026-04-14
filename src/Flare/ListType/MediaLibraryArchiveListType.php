<?php

namespace HeimrichHannot\MediaLibraryBundle\Flare\ListType;

use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListType;
use HeimrichHannot\FlareBundle\Enum\SqlEquationOperator;
use HeimrichHannot\FlareBundle\Event\ListSpecificationCreatedEvent;
use HeimrichHannot\FlareBundle\FilterElement\PublishedElement;
use HeimrichHannot\FlareBundle\FilterElement\SimpleEquationElement;
use HeimrichHannot\FlareBundle\ListType\AbstractListType;
use HeimrichHannot\FlareBundle\Query\JoinTypeEnum;
use HeimrichHannot\FlareBundle\Query\SqlJoinStruct;
use HeimrichHannot\FlareBundle\Query\TableAliasRegistry;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsListType(self::TYPE, dataContainer: ItemContainer::TABLE, palette: '{archive_legend},ml_archive')]
class MediaLibraryArchiveListType extends AbstractListType implements MediaLibraryFilesListTypeInterface
{
    public const TYPE = 'ml_archive';
    public const ALIAS_ARCHIVE = 'ml_archive';

    public function configureTableRegistry(TableAliasRegistry $registry): void
    {
        $fromAlias = TableAliasRegistry::ALIAS_MAIN;

        $registry->registerJoin(new SqlJoinStruct(
            fromAlias: $fromAlias,
            joinType: JoinTypeEnum::INNER,
            table: 'tl_ml_archive',
            joinAlias: self::ALIAS_ARCHIVE,
            condition: $registry->makeJoinOn(self::ALIAS_ARCHIVE, 'id', $fromAlias, 'pid')
        ));
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