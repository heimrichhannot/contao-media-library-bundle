<?php

namespace HeimrichHannot\MediaLibraryBundle\Flare\ListType;

use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListCallback;
use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListType;
use HeimrichHannot\FlareBundle\FilterElement\PublishedElement;
use HeimrichHannot\FlareBundle\List\ListQueryBuilder;
use HeimrichHannot\FlareBundle\List\PresetFiltersConfig;
use HeimrichHannot\FlareBundle\ListType\AbstractListType;

#[AsListType(alias: self::TYPE, dataContainer: 'tl_ml_item', palette: '{archive_legend},ml_archive')]
class MediaLibraryArchiveListType extends AbstractListType
{
    public const TYPE = 'ml_archive';
    public const ALIAS_ARCHIVE = 'ml_archive';

    #[AsListCallback(self::TYPE, 'query.configure')]
    public function prepareQuery(ListQueryBuilder $builder): void
    {
        $builder->innerJoin(
            table: 'tl_ml_archive',
            as: self::ALIAS_ARCHIVE,
            on: $builder->makeJoinOn(self::ALIAS_ARCHIVE, 'id', 'pid')
        );
    }

    #[AsListCallback(self::TYPE, 'preset_filters')]
    public function getPresetFilters(PresetFiltersConfig $config): void
    {
        $config->add(PublishedElement::define(), true);
    }
}