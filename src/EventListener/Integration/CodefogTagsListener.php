<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Integration;

use Codefog\TagsBundle\Model\TagModel;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use HeimrichHannot\FlareBundle\Event\ListQueryPrepareEvent;
use HeimrichHannot\FlareBundle\Exception\FlareException;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use HeimrichHannot\MediaLibraryBundle\Flare\ListType\MediaLibraryArchiveListType;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * Integrates the media library with the Codefog Tags Bundle.
 * @see https://github.com/codefog/tags-bundle
 */
readonly class CodefogTagsListener
{
    public const TABLE = ItemContainer::TABLE;

    public const CFG_TAG_ASSOCIATION_TABLE = 'tl_cfg_tag_ml_item';
    public const CFG_TAG_ASSOCIATION_TAG_FIELD = 'cfg_tag_id';
    public const CFG_TAG_ASSOCIATION_ITEM_FIELD = 'ml_item_id';

    public const ALIAS_TAG_TABLE = 'cfg_tag';
    public const ALIAS_JOIN_TABLE = 'cfg_tag_ml_item';

    public function __construct(
        private Connection $connection,
    ) {}

    /** @noinspection PhpUnused */
    #[AsCallback(self::TABLE, 'config.ondelete')]
    public function deleteTagAssociations(DataContainer $dc, int $undoId): void
    {
        $qTagTable = $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TABLE);
        $qItemField = $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_ITEM_FIELD);
        $qTagField = $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TAG_FIELD);

        $tagAssociations = Database::getInstance()
            ->prepare(\sprintf(
                'SELECT * FROM %s WHERE %s = ?',
                $qTagTable,
                $qItemField,
            ))
            ->execute($dc->id);

        if (!$tagAssociations || !$tagAssociations->numRows) {
            return;
        }

        while ($tagAssociations->next())
        {
            $tagId = (int) $tagAssociations->{self::CFG_TAG_ASSOCIATION_TAG_FIELD};
            $itemId = (int) $tagAssociations->{self::CFG_TAG_ASSOCIATION_ITEM_FIELD};

            $tagUsedByOtherRecord = $this->connection->executeQuery(
                \sprintf('SELECT 1 FROM %s WHERE %s = ? AND %s != ? LIMIT 1', $qTagTable, $qTagField, $qItemField),
                [$tagId, $dc->id],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );

            if ($tagUsedByOtherRecord->rowCount() > 0)
            {
                $this->connection->executeStatement(
                    \sprintf(
                        'DELETE FROM %s WHERE %s = ?',
                        $this->connection->quoteIdentifier(TagModel::getTable()),
                        $this->connection->quoteIdentifier('id'),
                    ),
                    [$tagId],
                    [ParameterType::INTEGER]
                );
            }

            $this->connection->executeStatement(
                \sprintf('DELETE FROM %s WHERE %s = ? AND %s = ?', $qTagTable, $qTagField, $qItemField),
                [$tagId, $itemId],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );
        }
    }

    /** @noinspection PhpUnused */
    #[AsCallback(self::TABLE, 'config.onsubmit')]
    public function updateTagAssociations(DataContainer $dc): void
    {
        Controller::loadDataContainer(TagModel::getTable());

        $source = $GLOBALS['TL_DCA'][self::TABLE]['fields']['tags']['eval']['tagsManager'];

        if (!$tags = TagModel::findBy(['source=?'], [$source])) {
            return;
        }

        if (!$tags->count()) {
            return;
        }

        $ids = [];
        $tagsInUse = $this->getUsedTagIds();

        while ($tags->next())
        {
            $tagId = (int) $tags->id;

            if (!\in_array($tagId, $tagsInUse, true))
            {
                $ids[] = $tagId;
            }
        }

        if (!empty($ids))
        {
            $this->connection->executeStatement(
                \sprintf(
                    'DELETE FROM %s WHERE %s IN (?)',
                    $this->connection->quoteIdentifier(TagModel::getTable()),
                    $this->connection->quoteIdentifier('id'),
                ),
                [$ids],
                [ArrayParameterType::INTEGER]
            );
        }
    }

    protected function getUsedTagIds(): array
    {
        $tagIdAlias = 'tag_id';

        $records = Database::getInstance()
            ->prepare(\sprintf(
                'SELECT DISTINCT %s AS %s FROM %s',
                $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TAG_FIELD),
                $this->connection->quoteIdentifier($tagIdAlias),
                $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TABLE),
            ))
            ->execute();

        if (!$records || $records->numRows < 1) {
            return [];
        }

        return \array_unique(\array_map('\intval', \array_filter($records->fetchEach($tagIdAlias))));
    }

    /**
     * @throws FlareException If a join to the cfg_tag_ml_item table fails.
     */
    #[AsEventListener]
    public function prepareQuery(ListQueryPrepareEvent $event): void
    {
        if ($event->listSpecification->type !== MediaLibraryArchiveListType::TYPE) {
            return;
        }

        $builder = $event->getListQueryBuilder();

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