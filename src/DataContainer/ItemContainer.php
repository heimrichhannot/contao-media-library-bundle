<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

class ItemContainer
{
    public const TABLE = 'tl_ml_item';

    #[AsCallback(self::TABLE, 'config.oncreate')]
    public function onCreateConfig(string $table, int $insertId, array $record, DataContainer $dc): void
    {
        if (!$insertId || !$pid = $record['pid'] ?? null) {
            return;
        }

        if (!$archive = ArchiveModel::findByPk($pid)) {
            return;
        }

        Database::getInstance()
            ->prepare("UPDATE `{$table}` SET `type` = ? WHERE `id` = ?")
            ->execute($archive->type, $insertId);
    }

    /**
     * @param DataContainer $dc
     * @return void
     * @throws \Exception
     * @todo(@ericges): Check if this handling has to change or if it can be removed altogether with v2.
     */
    #[AsCallback(self::TABLE, 'config.onload')]
    public function addAdditionalFields(DataContainer $dc): void
    {
        if (!$dc->id || !$product = ItemModel::findByPk($dc->id)) {
            return;
        }

        if (!$productArchive = $product->getRelated('pid')) {
            return;
        }

        if (!$additionalFields = StringUtil::deserialize($productArchive->additionalFields, true)) {
            return;
        }

        $palettes = &$GLOBALS['TL_DCA'][ItemModel::getTable()]['palettes'];

        $palettes[$product->type] = \str_replace(
            '{additional_fields_legend}',
            '{additional_fields_legend},'.implode(',', $additionalFields),
            $palettes[$product->type]
        );
    }
}