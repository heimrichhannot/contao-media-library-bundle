<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Backend;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;

class ProductArchive
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /**
     * get fields from tl_ml_product.
     *
     * @return mixed
     */
    public function getPaletteFields()
    {
        return System::getContainer()
            ->get('huh.utils.dca')
            ->getFields('tl_ml_product',
                ['inputTypes' => ['text', 'textarea', 'select', 'multifileupload', 'checkbox', 'tagsinput'], 'localizeLabels' => false]);
    }

    /**
     * get image sizes.
     *
     * @return mixed
     */
    public function getImageSizes()
    {
        $user = BackendUser::getInstance();
        $imageSizes = System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser($user);

        return $imageSizes['image_sizes'];
    }

    /**
     * get fields that have been selected in palette.
     *
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function getFieldsForTag(DataContainer $dc)
    {
        return \deserialize($dc->activeRecord->palette, true);
    }
}
