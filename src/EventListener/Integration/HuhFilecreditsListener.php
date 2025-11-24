<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Integration;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\DataContainer;
use Contao\FilesModel;
use HeimrichHannot\FileCreditsBundle\DataContainer\FileCreditContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Integrates the media library with the Heimrich & Hannot File Credits Bundle.
 * @see https://github.com/heimrichhannot/contao-filecredits-bundle
 */
class HuhFilecreditsListener
{
    public const COPYRIGHT_FIELD_NAME = '_filecredits_copyright';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {}

    #[AsHook("loadDataContainer")]
    public function addFilecreditsCopyrightField(string $table): void
    {
        if ($table !== ItemModel::getTable()) {
            return;
        }

        if (!\class_exists(FileCreditContainer::class)) {
            return;
        }

        $field = 'filecredits_copyright';

        FileCreditContainer::addCopyrightFieldToDca($table, $field, 'file');

        $GLOBALS['TL_DCA'][$table]['fields'][$field]['eval']['tl_class'] = 'clr';
    }

    #[AsCallback(table: ItemContainer::TABLE, target: 'config.onload')]
    public function onLoadItemContainer(?DataContainer $dc = null): void
    {
        $filesTable = FilesModel::getTable();
        DataContainer::loadDataContainer($filesTable);
        DataContainer::loadLanguageFile($filesTable);

        if (!$copyrightField = $GLOBALS['TL_DCA'][$filesTable]['fields']['copyright'] ?? null) {
            return;
        }

        if (!$itemDca = &$GLOBALS['TL_DCA'][ItemContainer::TABLE]) {
            return;
        }

        unset($copyrightField['sql']);
        $copyrightField['eval']['tl_class'] = 'clr w100';
        $copyrightField['eval']['doNotSaveEmpty'] = true;
        $copyrightField['eval']['versionize'] = false;
        $copyrightField['load_callback'][] = [__CLASS__, 'onLoadItemCopyrightField'];
        $copyrightField['save_callback'][] = [__CLASS__, 'onSaveItemCopyrightField'];

        $itemDca['fields'][self::COPYRIGHT_FIELD_NAME] = $copyrightField;
    }

    public function onLoadItemCopyrightField(mixed $value, DataContainer $dc): mixed
    {
        if (!$request = $this->requestStack->getCurrentRequest()) {
            return null;
        }

        if ($request->getMethod() === 'POST') {
            $fc = (array) $request->request->get('_filecredits_copyright', []);
            return \serialize(\array_filter(\array_unique($fc)));
        }

        if (!$dc->id || !$file = ItemModel::findByPk($dc->id)?->getFile()) {
            return null;
        }

        return $file->copyright;
    }

    public function onSaveItemCopyrightField(mixed $value, DataContainer $dc): null
    {
        if (!$dc->id || !$file = ItemModel::findByPk($dc->id)?->getFile()) {
            return null;
        }

        if (\is_array($value)) {
            $value = \serialize(\array_filter(\array_unique($value)));
        }

        $file->copyright = $value;
        $file->save();

        return null;
    }

    /*
     * Prepared for Contao 5 when 4.13 support is dropped.
     *
    #[AsCallback(table: ItemContainer::TABLE, target: 'config.onbeforesubmit')]
    public function onBeforeSubmitItemContainer(array $record, DataContainer $dc): array
    {
        $copyright = $record[self::COPYRIGHT_FIELD_NAME] ?? null;

        unset(
            $record[self::COPYRIGHT_FIELD_NAME],
            $GLOBALS['TL_DCA'][ItemContainer::TABLE]['fields'][self::COPYRIGHT_FIELD_NAME],
        );

        if (!$fileUuid = $record['file'] ?? null) {
            return $record;
        }

        if ($file = FilesModel::findByUuid($fileUuid)) {
            return $record;
        }

        $file->copyright = $copyright;
        $file->save();

        return $record;
    }
    */
}