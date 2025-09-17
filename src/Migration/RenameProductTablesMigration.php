<?php

namespace HeimrichHannot\MediaLibraryBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class RenameProductTablesMigration implements MigrationInterface
{
    public function __construct(
        private readonly Connection $connection,
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

        if ($tlMlProduct && $tlMlItem) {
            throw $this->createItemTableException();
        }

        if ($tlMlProductArchive && $tlMlArchive) {
            throw $this->createArchiveTableException();
        }

        $migrate = [];

        if ($tlMlProduct) {
            $migrate['tl_ml_product'] = 'tl_ml_item';
        }

        if ($tlMlProductArchive) {
            $migrate['tl_ml_product_archive'] = 'tl_ml_archive';
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
}