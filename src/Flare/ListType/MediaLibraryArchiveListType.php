<?php

namespace HeimrichHannot\MediaLibraryBundle\Flare\ListType;

use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListCallback;
use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListType;
use HeimrichHannot\FlareBundle\Exception\FlareException;
use HeimrichHannot\FlareBundle\FilterElement\PublishedElement;
use HeimrichHannot\FlareBundle\List\ListQueryBuilder;
use HeimrichHannot\FlareBundle\List\PresetFiltersConfig;
use HeimrichHannot\FlareBundle\ListType\AbstractListType;
use HeimrichHannot\MediaLibraryBundle\EventListener\Flare\JoinCodefogTagsCallback;

#[AsListType(alias: self::TYPE, dataContainer: 'tl_ml_item', palette: '{archive_legend},ml_archive')]
class MediaLibraryArchiveListType extends AbstractListType
{
    public const TYPE = 'ml_archive';
    public const ALIAS_ARCHIVE = 'ml_archive';

    /**
     * Configures the query by preparing an inner join on the specified table.
     *
     * @param ListQueryBuilder $builder The query builder instance used for constructing the query.
     * @throws FlareException if there is an error during query preparation.
     * @see JoinCodefogTagsCallback for joining tags.
     * @internal This method is intended for internal use only and should not be called directly.
     */
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