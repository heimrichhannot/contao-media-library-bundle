<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Model;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property string $type
 * @property string $additionalFields
 * @property bool   $keepProductTitleForDownloadItems
 * @property bool   $includeDelete
 * @property bool   $redirectAfterDelete
 * @property string $groupsCanDeleteAll
 * @property string $groupsCanDeleteOwn
 * @property bool   $protected
 */
class ProductArchiveModel extends Model
{
    protected static $strTable = 'tl_ml_product_archive';
}
