<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

class ArchiveContainer
{
    public const TABLE = 'tl_ml_archive';

    public function __construct(
        private readonly Connection $connection,
    ) {}

    #[AsCallback(self::TABLE, 'config.onsubmit')]
    public function onSubmit(DataContainer $dc): void
    {
        if (!$dc->id) {
            return;
        }

        $table = $this->connection->quoteIdentifier(self::TABLE);
        $stmt = $this->connection->prepare(<<<SQL
            UPDATE {$table}
               SET `dateAdded` = :dateAdded
             WHERE `id` = :id
               AND COALESCE(CAST(`dateAdded` AS UNSIGNED), 0) < 1
            SQL);
        $stmt->bindValue('dateAdded', \time(), ParameterType::INTEGER);
        $stmt->bindValue('id', $dc->id, ParameterType::INTEGER);
        $stmt->executeStatement();
    }

    #[AsCallback(self::TABLE, 'config.oncopy')]
    public function onCopy(int $id, DataContainer $dc): void
    {
        $this->connection->update(self::TABLE, ['dateAdded' => \time()], ['id' => $id]);
    }
}