<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\ParameterType;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Util\Str;
use Symfony\Component\HttpFoundation\RequestStack;

class ArchiveContainer
{
    public const TABLE = 'tl_ml_archive';

    public function __construct(
        private readonly ArchiveTypeCollection $archiveTypes,
        private readonly Connection            $connection,
        private readonly RequestStack          $requestStack,
    ) {}

    /**
     * @throws DBALException When the database query fails for some reason.
     */
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

    /**
     * Dynamically generate and add the palette for the current archive type if it does not exist yet.
     *
     * @throws \Exception When the DCA for the table cannot be loaded.
     */
    #[AsCallback(self::TABLE, 'config.onload')]
    public function onLoadGeneratePalette(?DataContainer $dc = null): void
    {
        $act = $this->requestStack->getCurrentRequest()?->query?->get('act');

        if (!$dc?->id || $act !== 'edit') {
            return;
        }

        if (!$archive = ArchiveModel::findByPk($dc->id)) {
            return;
        }

        if (!$type = (string) $archive->type) {
            return;
        }

        $dca = &$GLOBALS['TL_DCA'][self::TABLE];

        if (!\is_array($palettes = $dca['palettes'] ?? null)) {
            throw new \Exception('Unable to load DCA for ' . self::TABLE);
        }

        if ($palettes[$type] ?? null) {
            return;
        }

        if (!$archiveType = $this->archiveTypes->get($type)) {
            return;
        }

        $archivePalette = $archiveType->getArchivePalette($archive);

        $prefix = $dca['palettes']['__prefix__'] ?? '';
        $suffix = $dca['palettes']['__suffix__'] ?? '';

        $dca['palettes'][$type] = Str::mergePalettes($prefix, $archivePalette, $suffix);
    }

    #[AsCallback(self::TABLE,  'fields.additionalFields.options')]
    public function getAdditionalFieldsOptions(): array
    {
        Controller::loadDataContainer(ItemContainer::TABLE);
        Controller::loadLanguageFile(ItemContainer::TABLE);

        if (!$dca = $GLOBALS['TL_DCA'][ItemContainer::TABLE] ?? null) {
            return [];
        }

        if (!\is_array($fields = $dca['fields'] ?? null)) {
            return [];
        }

        $options = [];

        foreach ($fields as $fieldName => $field)
        {
            if (!($field['eval']['isAdditionalField'] ?? false)) {
                continue;
            }

            $label = $field['label'][0] ?? $fieldName;
            $options[$fieldName] = "$label [$fieldName]";
        }

        return $options;
    }
}