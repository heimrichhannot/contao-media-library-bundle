<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Versions;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;

#[AsCallback(ItemContainer::TABLE, 'config.onsubmit')]
class UpdateCopyrightListener
{
    public function __invoke(DataContainer $dc): void
    {
        if (!$dc->activeRecord || !$dc->activeRecord->file) {
            return;
        }

        $file = StringUtil::deserialize($dc->activeRecord->file, true);

        if (empty($file)) {
            return;
        }

        $model = FilesModel::findByUuid($file[0]);

        if (null === $model) {
            return;
        }

        $versions = new Versions('tl_files', $model->id);
        $versions->initialize();

        $model->copyright = $dc->activeRecord->copyright ?? null;
        $model->save();

        $versions->create();
    }
}