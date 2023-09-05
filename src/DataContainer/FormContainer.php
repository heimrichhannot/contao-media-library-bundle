<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;

class FormContainer
{
    /**
     * @Callback(table="tl_form", target="fields.ml_archive.options")
     */
    public function onFieldsMlArchiveOptionsCallback(): array
    {
        $options = [];
        $archives = ProductArchiveModel::findAll();

        if (null === $archives) {
            return $options;
        }

        foreach ($archives as $archive) {
            $options[$archive->id] = $archive->title;
        }

        return $options;
    }
}
