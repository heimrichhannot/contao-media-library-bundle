<?php

namespace HeimrichHannot\MediaLibraryBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

readonly class RenameProductTablesMigration implements MigrationInterface
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function getName(): string
    {
        return 'Media library: Rename product tables migration';
    }

    /**
     * @throws \Exception
     */
    public function shouldRun(): bool
    {
        $migrate = $this->checkTables();

        return \count($migrate) > 0;
    }

    /**
     * @throws \Exception
     */
    public function run(): MigrationResult
    {
        $migrate = $this->checkTables();

        // rename tables
        foreach ($migrate as $from => $to)
        {
            $from = $this->connection->quoteIdentifier($from);
            $to = $this->connection->quoteIdentifier($to);
            $this->connection->executeStatement("RENAME TABLE $from TO $to");
        }

        // check if tables have been renamed
        $tables = \array_fill_keys($this->connection->createSchemaManager()->listTableNames(), true);
        foreach ($migrate as $from => $to)
        {
            if (!($tables[$to] ?? false))
            {
                return new MigrationResult(false, <<<MSG
                    Migration of the media library tables failed successfully.
                    The table "$from" could not be renamed to "$to".
                    Please investigate and rename the table manually.
                    MSG);
            }
        }

        return new MigrationResult(true, "Migration to rename media library product tables completed.");
    }

    /**
     * @throws \Exception
     */
    private function checkTables(): array
    {
        $schemaManager = $this->connection->createSchemaManager();

        $tables = \array_fill_keys($schemaManager->listTableNames(), true);

        $tlMlProduct = $tables['tl_ml_product'] ?? false;
        $tlMlItem = $tables['tl_ml_item'] ?? false;
        $tlMlProductArchive = $tables['tl_ml_product_archive'] ?? false;
        $tlMlArchive = $tables['tl_ml_archive'] ?? false;
        $tlCfgTagMlProduct = $tables['tl_cfg_tag_ml_product'] ?? false;
        $tlCtgTagMlItem = $tables['tl_ctg_tag_ml_item'] ?? false;

        if ($tlMlProduct && $tlMlItem) {
            throw $this->createItemTableException();
        }

        if ($tlMlProductArchive && $tlMlArchive) {
            throw $this->createArchiveTableException();
        }

        if ($tlCfgTagMlProduct && $tlCtgTagMlItem) {
            throw $this->createTagTableException();
        }

        $migrate = [];

        if ($tlMlProduct) {
            $migrate['tl_ml_product'] = 'tl_ml_item';
        }

        if ($tlMlProductArchive) {
            $migrate['tl_ml_product_archive'] = 'tl_ml_archive';
        }

        if ($tlCfgTagMlProduct) {
            $migrate['tl_cfg_tag_ml_product'] = 'tl_ctg_tag_ml_item';
        }

        return $migrate;
    }

    private function createItemTableException(): \Exception
    {
        return new \Exception(<<<MSG
            There are conflicting media library product/item tables. Please rename the tables manually before proceeding with the migrations.
            There should only be one table of either 'tl_ml_product' (legacy) or 'tl_ml_item' (new).
            MSG);
    }

    private function createArchiveTableException(): \Exception
    {
        return new \Exception(<<<MSG
            There are conflicting media library archive tables. Please rename the tables manually before proceeding with the migrations.
            There should only be one table of either 'tl_ml_product_archive' (legacy) or 'tl_ml_archive' (new).
            MSG);
    }

    private function createTagTableException(): \Exception
    {
        return new \Exception(<<<MSG
            There are conflicting codefog tag tables. Please rename the tables manually before proceeding with the migrations.
            There should only be one table of either 'tl_cfg_tag_ml_product' (legacy) or 'tl_ctg_tag_ml_item' (new).
        MSG);
    }
}