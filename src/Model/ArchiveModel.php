<?php

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ArchiveContainer;

/**
 * Reads and writes media library archives.
 *
 * @property int           $id
 * @property int           $tstamp
 * @property int           $dateAdded
 * @property string        $title
 * @property string        $type
 * @property string        $additionalFields
 * @property bool          $enableCreate
 * @property bool          $enableEdit
 * @property int           $editJumpTo
 * @property bool          $enableDelete
 * @property bool          $deleteJumpTo
 * @property string        $groupsCanDeleteAll
 * @property string        $groupsCanDeleteOwn
 * @property bool          $protected
 * @property array|string  $imageSizes
 */
class ArchiveModel extends Model
{
    protected static $strTable = ArchiveContainer::TABLE;
    private array $arrImageSizes;

    public function getImageSizes(): array
    {
        if (isset($this->arrImageSizes)) {
            return $this->arrImageSizes;
        }

        $this->arrImageSizes = \is_array($this->imageSizes)
            ? $this->imageSizes
            : StringUtil::deserialize($this->imageSizes, true);

        return $this->arrImageSizes;
    }

    public function setImageSizes(array $imageSizes): void
    {
        $this->arrImageSizes = $imageSizes;
        $this->imageSizes = $imageSizes;
    }
}