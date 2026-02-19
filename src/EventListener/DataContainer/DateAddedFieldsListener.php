<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;

class DateAddedFieldsListener
{
    public const TABLE_ML_ARCHIVE = 'tl_ml_archive';
    public const TABLE_ML_ITEM = 'tl_ml_item';

    public function __construct(
        private readonly Connection $connection,
    ) {}

    /**
     * @throws DBALException When the database query fails for some reason.
     */
    #[AsCallback(self::TABLE_ML_ARCHIVE, 'config.onsubmit')]
    #[AsCallback(self::TABLE_ML_ITEM, 'config.onsubmit')]
    public function onSubmit(DataContainer $dc): void
    {
        if (!($id = $dc->id) || !($table = $dc->table)) {
            return;
        }

        $this->connection->createQueryBuilder()
            ->update($table)
            ->set('dateAdded', ':dateAdded')
            ->where('id = :id')
            ->andWhere('COALESCE(CAST(dateAdded AS UNSIGNED), 0) < 1')
            ->setParameter('dateAdded', \time(), ParameterType::INTEGER)
            ->setParameter('id', $id, ParameterType::INTEGER)
            ->executeStatement();
    }

    #[AsCallback(self::TABLE_ML_ARCHIVE, 'config.oncopy')]
    #[AsCallback(self::TABLE_ML_ITEM, 'config.oncopy')]
    public function onCopy(int $id, DataContainer $dc): void
    {
        if (!$table = $dc->table) {
            return;
        }

        $this->connection->update($table, ['dateAdded' => \time()], ['id' => $id]);
    }
}