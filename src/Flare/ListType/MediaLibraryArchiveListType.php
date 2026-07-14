<?php

namespace HeimrichHannot\MediaLibraryBundle\Flare\ListType;

use HeimrichHannot\FlareBundle\Config\ConfigBuilder;
use HeimrichHannot\FlareBundle\Contract\DcaContract;
use HeimrichHannot\FlareBundle\Contract\ListType\BuildListContract;
use HeimrichHannot\FlareBundle\DataContainer\Builder\DcaBuilder;
use HeimrichHannot\FlareBundle\DataContainer\Builder\DcaContext;
use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListType;
use HeimrichHannot\FlareBundle\Enum\SqlEquationOperator;
use HeimrichHannot\FlareBundle\Filter\Element\PublishedFilterElement;
use HeimrichHannot\FlareBundle\Filter\Element\SimpleEquationFilterElement;
use HeimrichHannot\FlareBundle\Filter\Filter;
use HeimrichHannot\FlareBundle\List\ListBuilder;
use HeimrichHannot\FlareBundle\List\Type\AbstractListType;
use HeimrichHannot\FlareBundle\Model\ListModel;
use HeimrichHannot\FlareBundle\Query\JoinTypeEnum;
use HeimrichHannot\FlareBundle\Query\SqlJoinStruct;
use HeimrichHannot\FlareBundle\Query\TableAliasRegistry;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[AsListType(self::TYPE, dataContainer: ItemContainer::TABLE)]
class MediaLibraryArchiveListType extends AbstractListType implements
    MediaLibraryFilesListTypeInterface, BuildListContract, DcaContract
{
    public const TYPE = 'ml_archive';
    public const ALIAS_ARCHIVE = 'ml_archive';

    public function buildDca(DcaBuilder $dca, DcaContext $context): void
    {
        $dca->palette('{archive_legend},ml_archive');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('ml_archive')->default(null)->allowedTypes('int', 'null');
    }

    protected function transformListModel(ConfigBuilder $config, ListModel $model): void
    {
        $config->set('ml_archive', $model->ml_archive ? ((int) $model->ml_archive) : null);
    }

    public function buildTableRegistry(TableAliasRegistry $registry): void
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

    public function buildList(ListBuilder $builder): void
    {
        if ($archiveId = (int) $builder->getModel()?->ml_archive) {
            $builder->addFilter(
                new Filter(
                    element: SimpleEquationFilterElement::TYPE,
                    config: [
                        'intrinsic' => true,
                        'left' => 'pid',
                        'operator' => SqlEquationOperator::EQUALS,
                        'right' => $archiveId,
                    ],
                ),
                '_ml_archive_id',
            );
        }

        if (!$builder->hasFilterOfType(PublishedFilterElement::TYPE)) {
            $builder->addFilter(new Filter(
                element: PublishedFilterElement::TYPE,
                config: [
                    'intrinsic' => true,
                    'published_field' => 'published',
                    'start_field' => 'start',
                    'stop_field' => 'stop',
                    'invert' => false,
                ],
            ));
        }
    }
}
