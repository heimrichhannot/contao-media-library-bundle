<?php

namespace HeimrichHannot\MediaLibraryBundle\Flare\ListType;

use HeimrichHannot\FlareBundle\Config\ConfigBuilder;
use HeimrichHannot\FlareBundle\DataContainer\Builder\DcaBuilderInterface;
use HeimrichHannot\FlareBundle\DataContainer\Builder\DcaContext;
use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListDriver;
use HeimrichHannot\FlareBundle\Enum\SqlEquationOperator;
use HeimrichHannot\FlareBundle\Filter\Element\PublishedFilterElement;
use HeimrichHannot\FlareBundle\Filter\Element\SimpleEquationFilterElement;
use HeimrichHannot\FlareBundle\Filter\Factory\FilterFactory;
use HeimrichHannot\FlareBundle\List\Driver\AbstractListDriver;
use HeimrichHannot\FlareBundle\List\ListSpecBuilder;
use HeimrichHannot\FlareBundle\Model\ListModel;
use HeimrichHannot\FlareBundle\Query\JoinTypeEnum;
use HeimrichHannot\FlareBundle\Query\SqlJoinStruct;
use HeimrichHannot\FlareBundle\Query\TableAliasRegistry;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use Symfony\Component\OptionsResolver\OptionsResolver;

#[AsListDriver(self::TYPE, dataContainer: ItemContainer::TABLE)]
class MediaLibraryArchiveListDriver extends AbstractListDriver implements MediaLibraryFilesListDriverInterface
{
    public const TYPE = 'ml_archive';
    public const ALIAS_ARCHIVE = 'ml_archive';

    public function __construct(private readonly FilterFactory $filterFactory) {}

    public function buildDca(DcaBuilderInterface $dca, DcaContext $context): void
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

    public function buildList(ListSpecBuilder $builder): void
    {
        $archiveId = (int) $builder->getModel()?->ml_archive;

        if (!$archiveId) {
            throw new \RuntimeException('No archive ID configured.');
        }

        $builder->addFilter($this->filterFactory->create(
            element: SimpleEquationFilterElement::TYPE,
            config: [
                'intrinsic' => true,
                'left' => 'pid',
                'operator' => SqlEquationOperator::EQUALS,
                'right' => $archiveId,
            ],
        ));

        if (!$builder->hasFilterInstance(PublishedFilterElement::class))
        {
            $builder->addFilter($this->filterFactory->create(
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
