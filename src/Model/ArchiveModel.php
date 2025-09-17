<?php

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
 * @property bool   $allowCreate
 * @property bool   $allowEdit
 * @property int    $editJumpTo
 * @property bool   $includeDelete
 * @property bool   $redirectAfterDelete
 * @property string $groupsCanDeleteAll
 * @property string $groupsCanDeleteOwn
 * @property bool   $protected
 */
class ArchiveModel extends Model
{
    protected static $strTable = 'tl_ml_archive';
}