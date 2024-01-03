<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\MemberModel;
use Contao\Model;
use Contao\StringUtil;

class ProductModel extends Model
{
    public const ITEM_LICENCE_TYPE_FREE = 'free';
    public const ITEM_LICENCE_TYPE_LOCKED = 'locked';

    protected static $strTable = 'tl_ml_product';

    public function memberCanDelete(MemberModel $member)
    {
        $productArchive = ProductArchiveModel::findByPk($this->pid);

        if ($productArchive === null) {
            return false;
        }

        if (!$productArchive->includeDelete) {
            return false;
        }

        $memberGroups = StringUtil::deserialize($member->groups, true);
        $groupsCanDeleteAll = StringUtil::deserialize($productArchive->groupsCanDeleteAll, true);

        if (!empty(array_intersect($memberGroups, $groupsCanDeleteAll))) {
            return true;
        }

        $groupsCanDeleteOwn = StringUtil::deserialize($productArchive->groupsCanDeleteOwn, true);

        return !empty(array_intersect($memberGroups, $groupsCanDeleteOwn)) && $this->author == $member->id;
    }
}
