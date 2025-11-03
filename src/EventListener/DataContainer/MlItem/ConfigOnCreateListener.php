<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer\MlItem;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;

#[AsCallback(table: 'tl_ml_item', target: 'config.oncreate')]
class ConfigOnCreateListener
{
    public function __construct(
        private readonly Connection $connection,
    )
    {
    }

    public function __invoke(string $table, int $insertId, array $record, DataContainer $dc): void
    {
        if (!$insertId || !$pid = $record['pid'] ?? null) {
            return;
        }

        if (!$archive = ArchiveModel::findByPk($pid)) {
            return;
        }

        $this->connection->update(
            'tl_ml_item',
            ['type' => $archive->type],
            ['id' => $insertId]
        );
    }
}