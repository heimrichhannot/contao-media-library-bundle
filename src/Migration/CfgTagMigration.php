<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class CfgTagMigration implements MigrationInterface
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Media Library CfgTag v3 Migration';
    }

    public function shouldRun(): bool
    {
        if (!$this->connection->getSchemaManager()->tablesExist('tl_cfg_tag')) {
            return false;
        }
        $result = $this->connection->executeQuery(
            "SELECT id FROM tl_cfg_tag WHERE source='huh.media_library.tags.product'"
        );

        if ($result->rowCount() > 0) {
            return true;
        }

        return false;
    }

    public function run(): MigrationResult
    {
        $result = $this->connection->executeStatement(
            "UPDATE tl_cfg_tag SET source='huh_media_library_product' WHERE source='huh.media_library.tags.product'"
        );

        return new MigrationResult(true, 'Finished Media Library CfgTag 3 Migration!');
    }
}
