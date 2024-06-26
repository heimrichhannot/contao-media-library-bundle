<?php

namespace HeimrichHannot\MediaLibraryBundle\Security;

use Contao\Controller;
use Contao\FrontendUser;
use Contao\MemberGroupModel;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProductVoter extends Voter
{
//    public const PERMISSION_CREATE = 'create_product';
    public const PERMISSION_EDIT = 'edit_product';
    public const PERMISSION_DELETE = 'delete_product';
    public const PERMISSION_DELETE_OWN = 'delete_own_product';

    public const PERMISSIONS = [
//        self::PERMISSION_CREATE,
        self::PERMISSION_EDIT,
        self::PERMISSION_DELETE,
        self::PERMISSION_DELETE_OWN
    ];

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, self::PERMISSIONS)) {
            return false;
        }

        if (!$subject instanceof ProductModel) {
            return false;
        }

        return true;
    }

    /**
     * @param string $attribute
     * @param ProductModel $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof FrontendUser) {
            return false;
        }

        $archiveModel = ProductArchiveModel::findByPk($subject->pid);
        if (!$archiveModel) {
            return false;
        }

        switch ($attribute) {
            // @todo implement create permission
//            case self::PERMISSION_CREATE:
//                break;
            case self::PERMISSION_EDIT:
                return $this->voteOnEdit($attribute, $user, $subject, $archiveModel);
            case self::PERMISSION_DELETE:
                return $this->voteOnDelete($archiveModel, $user, $subject);
        }

        return false;
    }

    private function voteOnEdit(string $attribute, FrontendUser $user, ProductModel $productModel, ProductArchiveModel $archiveModel): bool
    {
        if (!$archiveModel->allowEdit) {
            return false;
        }

        if ($productModel->author === $user->id) {
            return true;
        }

        if ($this->isAllowed(static::PERMISSION_EDIT, $user, $archiveModel)) {
            return true;
        }

        foreach ($user->groups as $group) {
            $groupModel = MemberGroupModel::findByPk($group);
            if ($groupModel && $this->isAllowed(static::PERMISSION_EDIT, $groupModel, $archiveModel)) {
                return true;
            }
        }

        return false;
    }

    private function voteOnDelete(ProductArchiveModel $archiveModel, FrontendUser $user, ProductModel $productModel): bool
    {
        if (!$archiveModel->includeDelete) {
            return false;
        }

        if ($this->isAllowed(self::PERMISSION_DELETE, $user, $archiveModel)) {
            return true;
        }

        foreach ($user->groups as $group) {
            $groupModel = MemberGroupModel::findByPk($group);
            if (!$groupModel) {
                continue;
            }
            if ($this->isAllowed(self::PERMISSION_DELETE, $groupModel, $archiveModel)) {
                return true;
            }
            if ($this->isAllowed(self::PERMISSION_DELETE_OWN, $groupModel, $archiveModel)
                && $productModel->author == $user->id) {
                return true;
            }
        }

        /**
         * Support old incorrect group permission fields
         * @deprecated Will be remove in next major version
         */
        if (!empty(array_intersect($user->groups, StringUtil::deserialize($archiveModel->groupsCanDeleteAll, true)))) {
            return true;
        }

        return !empty(array_intersect($user->groups, StringUtil::deserialize($archiveModel->groupsCanDeleteOwn, true)))
            && $productModel->author == $user->id;
    }

    private function isAllowed(string $attribute, FrontendUser|MemberGroupModel $user, ProductArchiveModel $archiveModel)
    {
        $archives = StringUtil::deserialize($user->ml_archives, true);
        if (!in_array($archiveModel->id, $archives)) {
            return false;
        }
        $accessRights = StringUtil::deserialize($user->ml_archivesp, true);
        if (!in_array($attribute, $accessRights)) {
            return false;
        }

        return true;
    }

    public static function createAccessRightFields(array &$dca): void
    {
        Controller::loadLanguageFile('tl_member');
        $dca['fields']['ml_archives'] = [
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'foreignKey'              => 'tl_ml_product_archive.title',
            'eval'                    => ['multiple'=>true],
            'sql'                     => "blob NULL"
        ];
        $dca['fields']['ml_archivesp'] = [
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'options'                 => self::PERMISSIONS,
            'reference'               => &$GLOBALS['TL_LANG']['tl_member']['ml_archivesp'],
            'eval'                    => ['multiple'=>true],
            'sql'                     => "blob NULL"
        ];
    }
}