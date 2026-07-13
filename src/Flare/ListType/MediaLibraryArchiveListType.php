<?php

namespace HeimrichHannot\MediaLibraryBundle\Flare\ListType;

use HeimrichHannot\FlareBundle\Contract\DcaContract;
use HeimrichHannot\FlareBundle\DataContainer\Builder\DcaBuilder;
use HeimrichHannot\FlareBundle\DataContainer\Builder\DcaContext;
use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListType;
use HeimrichHannot\FlareBundle\Enum\SqlEquationOperator;
use HeimrichHannot\FlareBundle\Event\ListSpecificationCreatedEvent;
use HeimrichHannot\FlareBundle\Filter\Element\PublishedFilterElement;
use HeimrichHannot\FlareBundle\Filter\Element\SimpleEquationFilterElement;
use HeimrichHannot\FlareBundle\ListType\AbstractListType;
use HeimrichHannot\FlareBundle\Query\JoinTypeEnum;
use HeimrichHannot\FlareBundle\Query\SqlJoinStruct;
use HeimrichHannot\FlareBundle\Query\TableAliasRegistry;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsListType(self::TYPE, dataContainer: ItemContainer::TABLE)]
class MediaLibraryArchiveListType extends AbstractListType implements MediaLibraryFilesListTypeInterface, DcaContract
{
    public const TYPE = 'ml_archive';
    public const ALIAS_ARCHIVE = 'ml_archive';

    public function buildDca(DcaBuilder $dca, DcaContext $context): void
    {
        $dca->palette('{archive_legend},ml_archive');
    }

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
        $spec = $config->listSpecification;

        if ($spec->type !== self::TYPE) {
            return;
        }

        if ($archiveId = $spec->ml_archive) {
            $spec->addFilter(
                SimpleEquationFilterElement::define('pid', SqlEquationOperator::EQUALS, $archiveId),
                '_ml_archive_id'
            );
        }

        if (!$spec->hasFilterOfType(PublishedFilterElement::TYPE)) {
            $spec->addFilter(PublishedFilterElement::define());
        }
    }
}
