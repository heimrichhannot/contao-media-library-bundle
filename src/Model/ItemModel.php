<?php

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Database;
use Contao\FilesModel;
use Contao\MemberModel;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ItemContainer;
use HeimrichHannot\UtilsBundle\Model\CfgTagModel;

/**
 * Reads and writes media library items.
 *
 * @property int $id
 * @property int $pid
 * @property string $type
 * @property string $title
 * @property string $alias
 * @property int $author
 * @property int $dateAdded
 * @property string $file
 * @property string|bool $addAdditionalFiles
 * @property array|string|null $additionalFiles
 * @property string|null $additionalFilesOrder
 * @property string|null $videoPosterImage
 * @property string|null $text
 * @property string|bool $published
 */
class ItemModel extends Model
{
    protected static $strTable = ItemContainer::TABLE;

    private array $_tags;
    private array $_variants;

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

    /**
     * @return FilesModel[]
     */
    public function getVariants(): array
    {
        if (!$this->addAdditionalFiles) {
            return [];
        }

        if (isset($this->_variants)) {
            return $this->_variants;
        }

        $this->_variants = [];

        if (!$additionalFiles = $this->additionalFiles) {
            return [];
        }

        if (!\is_array($additionalFiles)) {
            $additionalFiles = StringUtil::deserialize($additionalFiles, true);
        }

        if (!$additionalFiles = \array_filter($additionalFiles, static fn ($v) => \is_string($v) && $v !== '')) {
            return [];
        }

        $sqlUnhex = implode(',', array_fill(0, count($additionalFiles), 'UNHEX(?)'));
        $sqlParams = \array_map('bin2hex', $additionalFiles);

        $db = Database::getInstance();
        $result = $db
            ->prepare("SELECT * FROM `tl_files` WHERE `tl_files`.`uuid` IN ({$sqlUnhex}) AND `tl_files`.`type` = 'file'")
            ->execute($sqlParams);

        if (!$files = Model::createCollectionFromDbResult($result, FilesModel::getTable())) {
            return [];
        }

        $this->_variants = $files->getModels() ?? [];

        return $this->_variants;
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
