<?php

namespace HeimrichHannot\MediaLibraryBundle\Security;

use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ProductVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports($attribute, $subject): bool
    {
        if (!in_array($attribute, [self::EDIT, self::DELETE])) {
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
        $archiveModel = ProductArchiveModel::findByPk($subject->pid);
        if (!$archiveModel) {
            return false;
        }

        $user = $token->getUser();

        switch ($attribute) {
            case self::EDIT:
                if (!$archiveModel->allowEdit) {
                    return false;
                }

                return true;
            case self::DELETE:
        }
    }
}