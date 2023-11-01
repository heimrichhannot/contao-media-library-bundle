<?php

namespace HeimrichHannot\MediaLibraryBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class FixProductPublishedFieldMigration implements MigrationInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Fix product published field migration';
    }

    public function shouldRun(): bool
    {
        $result = $this->connection->executeQuery("SELECT id FROM tl_ml_product WHERE  published = '\''");
        return $result->rowCount() > 0;

    }

    public function run(): MigrationResult
    {
        $result = $this->connection->executeQuery("SELECT id FROM tl_ml_product WHERE  published = '\''");
        if ($result->rowCount() > 0) {
            $this->connection->executeQuery("UPDATE tl_ml_product SET published = 1 WHERE published = '\''");
        }
        return new MigrationResult(true, "Fixed product published field migration completed.");
    }
}