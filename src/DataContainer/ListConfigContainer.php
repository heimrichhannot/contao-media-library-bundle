<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ListConfigContainer
{
    public const SORTING_MODE_ML_ADDITIONAL_FILES = 'ml_additional_files';
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(ModelUtil $modelUtil)
    {
        $this->modelUtil = $modelUtil;
    }

    public function modifyDca(DataContainer $dc)
    {
        if (null === ($listConfig = $this->modelUtil->findModelInstanceByPk('tl_list_config', $dc->id))) {
            return;
        }

        if (null === ($filterConfig = $this->modelUtil->findModelInstanceByPk('tl_filter_config', $listConfig->filter))) {
            return;
        }

        if ('tl_ml_download' !== $filterConfig->dataContainer) {
            return;
        }

        $GLOBALS['TL_DCA']['tl_list_config']['fields']['sortingMode']['options'][] = static::SORTING_MODE_ML_ADDITIONAL_FILES;
    }
}
