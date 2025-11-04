<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\MediaLibraryBundle\Util\Str;

class ItemContainer
{
    /**
     * @api Table name can be used in userland callbacks etc.
     */
    public const TABLE = 'tl_ml_item';

    public function __construct(
        private readonly ArchiveTypeCollection $archiveTypes,
    ) {}

    #[AsCallback(self::TABLE, 'list.sorting.child_record')]
    public function childRecordCallback(array $row): string
    {
        $title = ($row['title'] ?? null) ?: ($row['id'] ?? null) ?: 'item';

        return \sprintf('<div class="tl_content_left">%s</div>', $title);
    }

    /**
     * @param DataContainer $dc
     * @return void
     * @throws \Exception
     * @todo(@ericges): Check if this handling has to change or if it can be removed altogether with v2.
     */
    #[AsCallback(self::TABLE, 'config.onload')]
    public function onLoadConfig(DataContainer $dc): void
    {
        if (!$dc->id || !$item = ItemModel::findByPk($dc->id)) {
            return;
        }

        if (!$archive = $item->getRelated('pid')) {
            return;
        }

        if (!$archive->type) {
            return;
        }

        $item->type = $archive->type;

        $dca = &$GLOBALS['TL_DCA'][self::TABLE];

        if (!\is_array($palettes = $dca['palettes'] ?? null)) {
            throw new \Exception('Unable to load DCA for ' . self::TABLE);
        }

        if (!($palettes[$archive->type] ?? null))
        {
            $palette = $this->createPalette($archive, $item);
            $dca['palettes'][$archive->type] = $palette;
            $dca['palettes']['default'] = $palette;
        }

        $this->appendAdditionalFieldsToPalette($archive);
    }

    public function createPalette(ArchiveModel $archive, ItemModel $item): string
    {
        if (!$archiveType = $this->archiveTypes->get($archive->type)) {
            throw new \Exception(\sprintf(
                'Unable to load archive type "%s" in %s::%s',
                $archive->type,
                self::class,
                __METHOD__
            ));
        }

        $itemPalette = $archiveType->getItemPalette($archive, $item);

        $prefix = $GLOBALS['TL_DCA'][self::TABLE]['palettes']['__prefix__'] ?? '';
        $suffix = $GLOBALS['TL_DCA'][self::TABLE]['palettes']['__suffix__'] ?? '';

        return Str::mergePalettes($prefix, $itemPalette, $suffix);
    }

    public function appendAdditionalFieldsToPalette(ArchiveModel $archive): void
    {
        $palette = $GLOBALS['TL_DCA'][self::TABLE]['palettes'][$archive->type] ?? '';

        if (!\str_contains($palette, '{additional_fields_legend}')) {
            return;
        }

        if (!$additionalFields = StringUtil::deserialize($archive->additionalFields, true)) {
            return;
        }

        $pm = PaletteManipulator::create();

        foreach ($additionalFields as $field) {
            $pm->addField($field, 'additional_fields_legend', PaletteManipulator::POSITION_APPEND);
        }

        $pm->applyToPalette($archive->type, self::TABLE);
    }
}