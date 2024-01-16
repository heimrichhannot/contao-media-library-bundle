<?php

namespace HeimrichHannot\MediaLibraryBundle\Security;

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

    public const PERMISSIONS = [
//        self::PERMISSION_CREATE,
        self::PERMISSION_EDIT,
        self::PERMISSION_DELETE
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
                return $this->voteOnEdit($attribute, $user, $archiveModel);
            case self::PERMISSION_DELETE:
                return $this->voteOnDelete($archiveModel, $user, $subject);
        }

        return false;
    }

    private function voteOnEdit(string $attribute, FrontendUser $user, ProductArchiveModel $archiveModel): bool
    {
        if (!$archiveModel->allowEdit) {
            return false;
        }

        $isAllowed = function ($entity) use ($archiveModel, $attribute) {
            $groups = StringUtil::deserialize($entity->ml_archives, true);
            if (!in_array($archiveModel->id, $groups)) {
                return false;
            }
            $accessRights = StringUtil::deserialize($entity->ml_archivesp, true);
            if (!in_array($attribute, $accessRights)) {
                return false;
            }

            return true;
        };

        if ($isAllowed($archiveModel->id, $user)) {
            return true;
        }

        foreach ($user->groups as $group) {
            $groupModel = MemberGroupModel::findByPk($group);
            if ($groupModel && $isAllowed($groupModel)) {
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

        if (!empty(array_intersect($user->groups, StringUtil::deserialize($archiveModel->groupsCanDeleteAll, true)))) {
            return true;
        }

        return !empty(array_intersect($user->groups, StringUtil::deserialize($archiveModel->groupsCanDeleteOwn, true)))
            && $productModel->author == $user->id;
    }

    public static function createAccessRightFields(array &$dca): void
    {
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
            'reference'               => &$GLOBALS['TL_LANG']['MSC'],
            'eval'                    => ['multiple'=>true],
            'sql'                     => "blob NULL"
        ];
    }
}