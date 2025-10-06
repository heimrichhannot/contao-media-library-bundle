<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Flare;

use HeimrichHannot\FlareBundle\DependencyInjection\Attribute\AsListCallback;
use HeimrichHannot\FlareBundle\Exception\FlareException;
use HeimrichHannot\FlareBundle\List\ListQueryBuilder;
use HeimrichHannot\MediaLibraryBundle\Flare\ListType\MediaLibraryArchiveListType;

class JoinCodefogTagsCallback
{
    public const ALIAS_TAG_TABLE = 'cfg_tag';
    public const ALIAS_JOIN_TABLE = 'cfg_tag_ml_item';

    /**
     * @throws FlareException
     */
    #[AsListCallback(MediaLibraryArchiveListType::TYPE, 'query.configure')]
    public function prepareQuery(ListQueryBuilder $builder): void
    {
        $builder
            ->leftJoin(
                table: 'tl_cfg_tag_ml_item',
                as: self::ALIAS_JOIN_TABLE,
                on: $builder->makeJoinOn(self::ALIAS_JOIN_TABLE, 'ml_item_id', 'id')
            )
            ->leftJoin(
                table: 'tl_cfg_tag',
                as: self::ALIAS_TAG_TABLE,
                on: $builder->makeJoinOn(
                    joinAlias: self::ALIAS_TAG_TABLE,
                    joinColumn: 'id',
                    relatedColumn: 'cfg_tag_id',
                    relatedAlias: self::ALIAS_JOIN_TABLE
                )
            )
            ->setTableAliasMandatory(self::ALIAS_JOIN_TABLE)
            ->setTableAliasMandatory(self::ALIAS_TAG_TABLE)
            ->setTableAliasHidden(self::ALIAS_JOIN_TABLE)
        ;
    }
}