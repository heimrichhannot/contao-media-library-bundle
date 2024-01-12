<?php

namespace HeimrichHannot\MediaLibraryBundle\Security;

use Contao\FrontendUser;
use Contao\MemberGroupModel;
use Contao\Model;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProductVoter extends Voter
{
    public const PERMISSION_CREATE = 'create_product';
    public const PERMISSION_EDIT = 'edit_product';
    public const PERMISSION_DELETE = 'delete_product';

    public const PERMISSIONS = [
        self::PERMISSION_CREATE,
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
     * @return void
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        $token->getAttributes();

        $archiveModel = ProductArchiveModel::findByPk($subject->pid);
        if (!$archiveModel || !$archiveModel->allowEdit) {
            return false;
        }

        $user = $token->getUser();

        if ($user instanceof FrontendUser) {
            return $this->voteForFrontendUser($attribute, $subject, $user, $archiveModel);
        }

        return false;
    }

    private function voteForFrontendUser(string $attribute, ProductModel $subject, FrontendUser $user, ProductArchiveModel $archiveModel): bool
    {
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