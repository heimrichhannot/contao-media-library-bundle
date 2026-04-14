<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\Event\ArchivePaletteEvent;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ArchiveContainer
{
    /**
     * @api Table name can be used in userland callbacks etc.
     */
    public const TABLE = 'tl_ml_archive';

    public function __construct(
        private readonly ArchiveTypeCollection    $archiveTypes,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack             $requestStack,
    ) {}

    /**
     * Dynamically generate and add the palette for the current archive type if it does not exist yet.
     *
     * @throws \Exception When the DCA for the table cannot be loaded.
     */
    #[AsCallback(self::TABLE, 'config.onload')]
    public function onLoadGeneratePalette(?DataContainer $dc = null): void
    {
        $act = $this->requestStack->getCurrentRequest()?->query?->get('act');

        if (!$dc || !$dc->id || $act !== 'edit') {
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

        /** @var ArchivePaletteEvent $event */
        $event = $this->eventDispatcher->dispatch(new ArchivePaletteEvent(
            archiveType: $type,
            archiveModel: $archive,
            palette: $archivePalette,
            prefix: $prefix,
            suffix: $suffix,
        ));

        $dca['palettes'][$type] = $event->assemblePalette();
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