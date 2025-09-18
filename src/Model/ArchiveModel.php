<?php

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Model;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ArchiveContainer;

/**
 * @property int    $id
 * @property int    $tstamp
 * @property int    $dateAdded
 * @property string $title
 * @property string $type
 * @property string $additionalFields
 * @property bool   $keepProductTitleForDownloadItems
 * @property bool   $enableCreate
 * @property bool   $enableEdit
 * @property int    $editJumpTo
 * @property bool   $enableDelete
 * @property bool   $deleteJumpTo
 * @property string $groupsCanDeleteAll
 * @property string $groupsCanDeleteOwn
 * @property bool   $protected
 */
class ArchiveModel extends Model
{
    protected static $strTable = ArchiveContainer::TABLE;
}