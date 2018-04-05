<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Model;

/**
 * @property string $title
 * @property string $category
 * @property string $uploadedFiles
 * @property string $text
 * @property string $tags
 */
class ProductModel extends Model
{
    const ITEM_LICENCE_TYPE_FREE = 'free';
    const ITEM_LICENCE_TYPE_LOCKED = 'locked';

    protected static $strTable = 'tl_ml_product';
}
