<?php

namespace HeimrichHannot\MediaLibraryBundle\Security;

use BackendUser;
use Contao\Controller;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\FrontendUser;
use Contao\MemberGroupModel;
use Contao\StringUtil;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as SymfonyVoter;

class Voter extends SymfonyVoter
{
    public const COLUMN_ARCHIVES = 'ml_archives';
    public const COLUMN_ARCHIVE_PERMISSIONS = 'ml_archivep';

    public const PERMISSION_CREATE = 'ml_item_create';
    public const PERMISSION_EDIT = 'ml_item_edit';
    public const PERMISSION_DELETE = 'ml_item_delete';
    public const PERMISSION_DELETE_OWN = 'ml_item_delete_own';

    public const PERMISSIONS = [
        self::PERMISSION_CREATE,
        self::PERMISSION_EDIT,
        self::PERMISSION_DELETE,
        self::PERMISSION_DELETE_OWN
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!\in_array($attribute, self::PERMISSIONS)) {
            return false;
        }

        if ($attribute === self::PERMISSION_CREATE) {
            return $subject instanceof ArchiveModel;
        }

        if (!$subject instanceof ItemModel) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if (!$subject instanceof ArchiveModel && !$subject instanceof ItemModel) {
            return false;
        }

        if ($attribute === self::PERMISSION_CREATE && !$subject instanceof ArchiveModel) {
            return false;
        }

        $user = $token->getUser();

        if ($user instanceof BackendUser) {
            return $user->isAdmin;
        }

        if (!$user instanceof FrontendUser) {
            return false;
        }

        $archiveModel = match ($subject::class) {
            ArchiveModel::class => $subject,
            ItemModel::class => $subject->pid ? ArchiveModel::findByPk($subject->pid) : null,
            default => null,
        };

        if (!$archiveModel) {
            return false;
        }

        return match ($attribute)
        {
            self::PERMISSION_CREATE => $this->voteOnCreate($user, $archiveModel),
            self::PERMISSION_EDIT => $this->voteOnEdit($user, $subject, $archiveModel),
            self::PERMISSION_DELETE => $this->voteOnDelete($archiveModel, $user, $subject),
            default => false,
        };
    }

    private function voteOnCreate(FrontendUser $user, ArchiveModel $archiveModel): bool
    {
        if (!$archiveModel->enableCreate)
        {
            return false;
        }

        if ($this->isAllowed(static::PERMISSION_CREATE, $user, $archiveModel))
        {
            return true;
        }

        foreach ($user->groups as $group)
        {
            if (!$groupModel = MemberGroupModel::findByPk($group)) {
                continue;
            }

            if ($this->isAllowed(static::PERMISSION_CREATE, $groupModel, $archiveModel)) {
                return true;
            }
        }

        return false;
    }

    private function voteOnEdit(FrontendUser $user, ItemModel $productModel, ArchiveModel $archiveModel): bool
    {
        if (!$archiveModel->enableEdit) {
            return false;
        }

        if ($user->id === (int) $productModel->author) {
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

    private function voteOnDelete(ArchiveModel $archiveModel, FrontendUser $user, ItemModel $productModel): bool
    {
        if (!$archiveModel->enableDelete) {
            return false;
        }

        if ($this->isAllowed(self::PERMISSION_DELETE, $user, $archiveModel)) {
            return true;
        }

        $authorId = (int) $productModel->author;
        $userId = (int) $user->id;
        $userIsAuthor = $authorId && $userId && $authorId === $userId;

        foreach ($user->groups as $group)
        {
            if (!$groupModel = MemberGroupModel::findByPk($group))
            {
                continue;
            }

            if ($this->isAllowed(self::PERMISSION_DELETE, $groupModel, $archiveModel))
            {
                return true;
            }

            if ($userIsAuthor && $this->isAllowed(self::PERMISSION_DELETE_OWN, $groupModel, $archiveModel))
            {
                return true;
            }
        }

        return false;
    }

    private function isAllowed(string $attribute, FrontendUser|MemberGroupModel $user, ArchiveModel $archiveModel): bool
    {
        if (!$user->ml_archives || !$archives = StringUtil::deserialize($user->ml_archives, true)) {
            return false;
        }

        if (!$user->ml_archivep || !$permissions = StringUtil::deserialize($user->ml_archivep, true)) {
            return false;
        }

        if (!\in_array($attribute, $permissions, true)) {
            return false;
        }

        $archives = \array_map('\intval', $archives);

        if (!\in_array((int) $archiveModel->id, $archives, true)) {
            return false;
        }

        return true;
    }

    public static function createAccessRightFields(string $table, string $paletteParent): void
    {
        Controller::loadLanguageFile('tl_member');

        $dca = &$GLOBALS['TL_DCA'][$table];

        $dca['fields'][self::COLUMN_ARCHIVES] = [
            'exclude' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_ml_archive.title',
            'eval' => ['multiple' => true],
            'sql' => "blob NULL",
        ];

        $dca['fields'][self::COLUMN_ARCHIVE_PERMISSIONS] = [
            'exclude' => true,
            'inputType' => 'checkbox',
            'options' => self::PERMISSIONS,
            'reference' => &$GLOBALS['TL_LANG']['tl_member']['ml_archivep'],
            'eval' => ['multiple' => true],
            'sql' => "blob NULL",
        ];

        PaletteManipulator::create()
            ->addLegend('media_library_legend', $paletteParent, PaletteManipulator::POSITION_AFTER, true)
            ->addField('ml_archives', 'media_library_legend', PaletteManipulator::POSITION_APPEND)
            ->addField('ml_archivep', 'media_library_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', $table);
    }
}