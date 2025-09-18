<?php

namespace HeimrichHannot\MediaLibraryBundle\Migration;

use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

readonly class RenameColumnsMigration implements MigrationInterface
{
    public function __construct(
        private Connection $connection,
    ) {}

    public function getName(): string
    {
        return 'Media library: Rename columns migration';
    }

    public function shouldRun(): bool
    {
        $migrate = $this->checkColumns();

        $any = false;
        foreach ($migrate as $columns)
        {
            if (\count($columns) > 0) {
                $any = true;
                break;
            }
        }

        return $any;
    }

    public function run(): MigrationResult
    {
        $migrate = $this->checkColumns();

        foreach ($migrate as $table => $columns)
        {
            $table = $this->connection->quoteIdentifier($table);

            foreach ($columns as $from => $to)
            {
                $from = $this->connection->quoteIdentifier($from);
                $to = $this->connection->quoteIdentifier($to);
                $this->connection->executeStatement("ALTER TABLE {$table} RENAME COLUMN {$from} TO {$to}");
            }
        }

        // check if columns have been renamed
        $schemaManager = $this->connection->createSchemaManager();
        $failures = [];

        foreach ($migrate as $table => $columns)
        {
            $currentColumns = $this->getColumnNames($schemaManager, $table);

            foreach ($columns as $from => $to)
            {
                if (!($currentColumns[$to] ?? false))
                {
                    $failures[] = "  - {$table}.{$from} => {$table}.{$to}";
                }
            }
        }

        if (\count($failures) > 0)
        {
            $listing = \implode("\n", $failures);
            return new MigrationResult(false, <<<MSG
                Migration of the media library columns failed successfully.
                The following columns could not be renamed:
                {$listing}
                MSG);
        }

        return new MigrationResult(true, 'Migration of the media library columns was successful.');
    }

    private function checkColumns(): array
    {
        $rename = [
            'tl_ml_archive' => [
                'allowCreate' => 'enableCreate',
                'allowEdit' => 'enableEdit',
                'includeDelete' => 'enableDelete',
                'redirectAfterDelete' => 'deleteJumpTo',
            ]
        ];

        $schemaManager = $this->connection->createSchemaManager();

        foreach ($rename as $table => $columns)
        {
            $currentColumns = $this->getColumnNames($schemaManager, $table);

            foreach ($columns as $from => $to)
            {
                if (!($currentColumns[$from] ?? false) || ($currentColumns[$to] ?? false))
                {
                    unset($rename[$table][$from]);
                }
            }
        }

        return \array_filter($rename);
    }

    private function getColumnNames(AbstractSchemaManager $schemaManager, string $table): array
    {
        $currentColumns = $schemaManager->listTableColumns($table);
        $currentColumns = \array_map(static fn($column) => $column->getName(), $currentColumns);

        return \array_fill_keys($currentColumns, true);
    }
}