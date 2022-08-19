<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Model;

class ProductModel extends Model
{
    public const ITEM_LICENCE_TYPE_FREE = 'free';
    public const ITEM_LICENCE_TYPE_LOCKED = 'locked';

    protected static $strTable = 'tl_ml_product';
}
