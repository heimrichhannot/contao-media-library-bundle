<?php

namespace HeimrichHannot\MediaLibraryBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class RenameCfgSourceMigration extends AbstractMigration
{
    public const CFG_TAG_TABLE = 'tl_cfg_tag';
    public const SOURCE_COLUMN = 'source';
    public const SOURCE_VALUE_OLD = 'huh_media_library_product';
    public const SOURCE_VALUE_NEW = 'huh_media_library_item';

    public function __construct(
        private readonly Connection $connection,
    ) {}

    public function getName(): string
    {
        return 'Media library: Rename codefog/tags-bundle source column value migration';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (!$schemaManager->tablesExist([self::CFG_TAG_TABLE])) {
            return false;
        }

        $qTable = $this->connection->quoteIdentifier(self::CFG_TAG_TABLE);
        $qColumn = $this->connection->quoteIdentifier(self::SOURCE_COLUMN);

        $found = $this->connection
            ->executeQuery("SELECT 1 FROM {$qTable} WHERE {$qColumn} = ? LIMIT 1", [self::SOURCE_VALUE_OLD])
            ->fetchOne();

        return (bool) $found;
    }

    public function run(): MigrationResult
    {
        $qTable = $this->connection->quoteIdentifier(self::CFG_TAG_TABLE);
        $qColumn = $this->connection->quoteIdentifier(self::SOURCE_COLUMN);

        $rows = $this->connection->executeQuery(
            "UPDATE {$qTable} SET {$qColumn} = ? WHERE {$qColumn} = ?",
            [self::SOURCE_VALUE_NEW, self::SOURCE_VALUE_OLD]
        )->rowCount();

        return new MigrationResult(true, 'Updated ' . $rows . ' rows in table ' . self::CFG_TAG_TABLE . '.');
    }
}