<?php

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Database;
use Contao\FilesModel;
use Contao\MemberModel;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;

class ItemModel extends Model
{
    public const ITEM_LICENCE_TYPE_FREE = 'free';
    public const ITEM_LICENCE_TYPE_LOCKED = 'locked';

    protected static $strTable = ItemContainer::TABLE;

    private array $_tags;

    /**
     * @param MemberModel $member
     * @return bool
     *
     * @deprecated Use Voter instead
     */
    public function memberCanDelete(MemberModel $member): bool
    {
        $productArchive = ArchiveModel::findByPk($this->pid);

        if ($productArchive === null) {
            return false;
        }

        if (!$productArchive->enableDelete) {
            return false;
        }

        $memberGroups = StringUtil::deserialize($member->groups, true);
        $groupsCanDeleteAll = StringUtil::deserialize($productArchive->groupsCanDeleteAll, true);

        if (!empty(array_intersect($memberGroups, $groupsCanDeleteAll))) {
            return true;
        }

        $groupsCanDeleteOwn = StringUtil::deserialize($productArchive->groupsCanDeleteOwn, true);

        return !empty(array_intersect($memberGroups, $groupsCanDeleteOwn)) && (int) $this->author === (int) $member->id;
    }

    public function getArchive(): ?ArchiveModel
    {
        if (!$archive = $this->getRelated('pid')) {
            return null;
        }

        if (!$archive instanceof ArchiveModel) {
            return null;
        }

        return $archive;
    }

    public function getFile(): ?FilesModel
    {
        if (!$this->file) {
            return null;
        }

        if (!$file = FilesModel::findByUuid($this->file)) {
            return null;
        }

        if (!$file instanceof FilesModel) {
            return null;
        }

        return $file;
    }

    public function getCodefogTags(): array
    {
        if (isset($this->_tags)) {
            return $this->_tags;
        }

        $db = Database::getInstance();
        $tagRows = $db->prepare('SELECT t.* FROM tl_cfg_tag_ml_item i LEFT JOIN tl_cfg_tag t ON t.id = i.cfg_tag_id WHERE i.ml_item_id = ?')
            ->execute($this->id);

        if (!$tagRows->numRows) {
            return $this->_tags = [];
        }

        $this->_tags = Model::createCollectionFromDbResult($tagRows, CfgTagModel::getTable())->getModels() ?? [];

        return $this->_tags;
    }

    public function getCodefogTagNames(): array
    {
        return array_map(static fn (CfgTagModel $tag) => $tag->name, $this->getCodefogTags());
    }
}
