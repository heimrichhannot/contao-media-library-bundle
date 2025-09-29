<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Contao;

use Codefog\TagsBundle\Model\TagModel;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class UpdateCategoryTagsListener
{
    public const TABLE = 'tl_ml_item';

    public const CFG_TAG_ASSOCIATION_TABLE = 'tl_cfg_tag_ml_item';
    public const CFG_TAG_ASSOCIATION_TAG_FIELD = 'cfg_tag_id';
    public const CFG_TAG_ASSOCIATION_ITEM_FIELD = 'ml_item_id';

    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function deleteTagAssociations(DataContainer $dc, int $undoId): void
    {
        $tagAssociations = Database::getInstance()
            ->prepare(\sprintf(
                'SELECT * FROM %s WHERE %s = ?',
                $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TABLE),
                $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_ITEM_FIELD),
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
                \sprintf(
                    'SELECT 1 FROM %s WHERE %s = ? AND %s != ? LIMIT 1',
                    $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TABLE),
                    $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TAG_FIELD),
                    $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_ITEM_FIELD),
                ),
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
                \sprintf(
                    'DELETE FROM %s WHERE %s = ? AND %s = ?',
                    $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TABLE),
                    $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_TAG_FIELD),
                    $this->connection->quoteIdentifier(self::CFG_TAG_ASSOCIATION_ITEM_FIELD),
                ),
                [$tagId, $itemId],
                [ParameterType::INTEGER, ParameterType::INTEGER]
            );
        }
    }

    #[AsCallback(self::TABLE, 'config.onsubmit')]
    public function updateTagAssociations(DataContainer $dc): void
    {
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

        return \array_unique(\array_map('intval', \array_filter($records->fetchEach($tagIdAlias))));
    }
}