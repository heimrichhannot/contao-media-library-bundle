<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use HeimrichHannot\FileCreditsBundle\DataContainer\FileCreditContainer;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

#[AsHook("loadDataContainer")]
readonly class AttachFilecreditsCopyrightListener
{
    public function __invoke(string $table): void
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
}
