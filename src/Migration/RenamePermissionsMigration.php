<?php

namespace HeimrichHannot\MediaLibraryBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Column;
use HeimrichHannot\MediaLibraryBundle\Security\Voter;

readonly class RenamePermissionsMigration implements MigrationInterface
{
    public const LEGACY_PERMISSION_CREATE = 'create_product';
    public const LEGACY_PERMISSION_EDIT = 'edit_product';
    public const LEGACY_PERMISSION_DELETE = 'delete_product';
    public const LEGACY_PERMISSION_DELETE_OWN = 'delete_own_product';

    public const PERMISSION_MAP = [
        self::LEGACY_PERMISSION_CREATE => Voter::PERMISSION_CREATE,
        self::LEGACY_PERMISSION_EDIT => Voter::PERMISSION_EDIT,
        self::LEGACY_PERMISSION_DELETE => Voter::PERMISSION_DELETE,
        self::LEGACY_PERMISSION_DELETE_OWN => Voter::PERMISSION_DELETE_OWN,
    ];

    public function __construct(
        private Connection $connection,
    ) {}

    public function getName(): string
    {
        return 'Media library: Rename permissions migration';
    }

    public function shouldRun(): bool
    {
        $tables = $this->checkLegacyPermissions();

        return \count($tables) > 0;
    }

    public function run(): MigrationResult
    {
        $tables = $this->checkLegacyPermissions();

        $column = Voter::COLUMN_ARCHIVE_PERMISSIONS;
        $qColumn = $this->connection->quoteIdentifier($column);
        $qId = $this->connection->quoteIdentifier('id');

        foreach ($tables as $table)
        {
            $qTable = $this->connection->quoteIdentifier($table);

            // fetch all rows
            $rows = $this->connection->fetchAllAssociative(<<<SQL
                SELECT {$qId}, {$qColumn}
                  FROM {$qTable}
                 WHERE {$qId} > 0
                   AND CAST({$qColumn} AS CHAR CHARACTER SET utf8mb4) LIKE '%\_product%'
            SQL);

            foreach ($rows as $row)
            {
                if (!$id = $row['id'] ?? null) {
                    continue;
                }

                if (!$permissions = StringUtil::deserialize($row[$column], true)) {
                    continue;
                }

                $update = [];

                foreach ($permissions as $permission)
                {
                    $value = self::PERMISSION_MAP[$permission] ?? $permission;

                    if (\str_contains($value, '_product'))
                        // just in case a permission is not mapped correctly, avoid infinite loops
                        // by removing any permission containing '_product' that has not been mapped
                    {
                        continue;
                    }

                    $update[] = $value;
                }

                $this->connection->update($table, [$column => \serialize($update)], ['id' => $id]);
            }
        }

        // check if any legacy permissions remain
        $migrate = $this->checkLegacyPermissions();
        if (\count($migrate) > 0)
        {
            $failures = \array_map(static fn (string $table) => "  - {$table}", $migrate);
            $listing = \implode("\n", $failures);
            return new MigrationResult(false, <<<MSG
                The migration of media library permissions failed.
                The following tables still contain legacy permissions:
                {$listing}
                MSG);
        }

        return new MigrationResult(true, 'The migration of media library permissions was successful.');
    }

    public function checkLegacyPermissions(): array
    {
        $tables = ['tl_member_group', 'tl_member'];
        $column = Voter::COLUMN_ARCHIVE_PERMISSIONS;
        $qColumn = $this->connection->quoteIdentifier($column);

        $migrate = [];
        $schemaManager = $this->connection->createSchemaManager();

        foreach ($tables as $table)
        {
            $currentColumns = $schemaManager->listTableColumns($table);
            $currentColumnNames = \array_map(static fn (Column $col): string => $col->getName(), $currentColumns);

            if (!\in_array($column, $currentColumnNames, true)) {
                continue;
            }

            $qTable = $this->connection->quoteIdentifier($table);
            $qId = $this->connection->quoteIdentifier('id');

            // find legacy permissions
            $result = $this->connection->executeQuery(<<<SQL
                SELECT 1
                  FROM {$qTable}
                 WHERE {$qId} > 0
                   AND CAST({$qColumn} AS CHAR CHARACTER SET utf8mb4) LIKE '%\_product%'
                 LIMIT 1
            SQL);

            if ($result->fetchOne() !== false) {
                $migrate[] = $table;
            }
        }

        return $migrate;
    }
}