<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\BackendUser;
use Contao\Controller;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\MemberGroupModel;
use Contao\RequestToken;
use Contao\StringUtil;
use Contao\System;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;

class ProductArchiveContainer
{
    protected FileUtil $fileUtil;

    protected ModelUtil $modelUtil;

    protected Security $security;

    public function __construct(
        FileUtil $fileUtil,
        ModelUtil $modelUtil,
        Security $security
    ) {
        $this->fileUtil = $fileUtil;
        $this->modelUtil = $modelUtil;
        $this->security = $security;
    }

    /**
     * get image sizes.
     *
     * @return mixed
     */
    public function getImageSizes()
    {
        $user = BackendUser::getInstance();
        $imageSizes = System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser($user);

        $options = [];

        foreach ($imageSizes as $key => $size) {
            if (\in_array($key, ['image_sizes', 'relative', 'exact'])) {
                continue;
            }

            foreach ($size as $id => $label) {
                $options[$id] = "$label [ID $id]";
            }
        }

        return $options;
    }

    public function checkPermission()
    {
        $user = BackendUser::getInstance();
        $database = Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!\is_array($user->contao_media_library_bundles) || empty($user->contao_media_library_bundles)) {
            $root = [0];
        } else {
            $root = $user->contao_media_library_bundles;
        }

        $GLOBALS['TL_DCA']['tl_ml_product_archive']['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$user->hasAccess('create', 'contao_media_library_bundlep')) {
            $GLOBALS['TL_DCA']['tl_ml_product_archive']['config']['closed'] = true;
        }

        /** @var SessionInterface $objSession */
        $objSession = System::getContainer()->get('session');

        // Check current action
        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!\in_array(Input::get('id'), $root, true)) {
                    /** @var AttributeBagInterface $sessionBag */
                    $sessionBag = $objSession->getBag('contao_backend');

                    $arrNew = $sessionBag->get('new_records');

                    if (\is_array($arrNew['tl_ml_product_archive']) && \in_array(Input::get('id'),
                            $arrNew['tl_ml_product_archive'], true)) {
                        // Add the permissions on group level
                        if ('custom' != $user->inherit)
                        {
                            $sql = "SELECT id, contao_media_library_bundles, contao_media_library_bundlep FROM tl_user_group WHERE id IN(%s);";
                            $sql = sprintf($sql, implode(',', array_map('intval', $user->groups)));

                            $objGroup = $database->execute($sql);

                            while ($objGroup->next()) {
                                $arrModulep = StringUtil::deserialize($objGroup->contao_media_library_bundlep);

                                if (\is_array($arrModulep) && \in_array('create', $arrModulep, true)) {
                                    $arrModules = StringUtil::deserialize($objGroup->contao_media_library_bundles,
                                        true);
                                    $arrModules[] = Input::get('id');

                                    $database->prepare('UPDATE tl_user_group SET contao_media_library_bundles=? WHERE id=?')->execute(
                                        serialize($arrModules),
                                        $objGroup->id
                                    );
                                }
                            }
                        }

                        // Add the permissions on user level
                        if ('group' != $user->inherit) {
                            $user = $database->prepare('SELECT contao_media_library_bundles, contao_media_library_bundlep FROM tl_user WHERE id=?')
                                ->limit(1)
                                ->execute($user->id);

                            $arrModulep = StringUtil::deserialize($user->contao_media_library_bundlep);

                            if (\is_array($arrModulep) && \in_array('create', $arrModulep, true)) {
                                $arrModules = StringUtil::deserialize($user->contao_media_library_bundles, true);
                                $arrModules[] = Input::get('id');

                                $database->prepare('UPDATE tl_user SET contao_media_library_bundles=? WHERE id=?')
                                    ->execute(serialize($arrModules), $user->id);
                            }
                        }

                        // Add the new element to the user object
                        $root[] = Input::get('id');
                        $user->contao_media_library_bundles = $root;
                    }
                }
            // no break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!\in_array(Input::get('id'), $root, true)
                    || ('delete' == Input::get('act')
                        && !$user->hasAccess(
                            'delete',
                            'contao_media_library_bundlep'
                        ))
                ) {
                    throw new AccessDeniedException('Not enough permissions to '. Input::get('act').' ml_product_archive ID '. Input::get('id').'.');
                }

                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->all();

                if ('deleteAll' == Input::get('act') && !$user->hasAccess('delete',
                        'contao_media_library_bundlep')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);

                break;

            default:
                if (\strlen(Input::get('act'))) {
                    throw new AccessDeniedException('Not enough permissions to '. Input::get('act').' ml_product_archives.');
                }

                break;
        }
    }

    public function checkIncludeDelete(DataContainer $dc)
    {
        $record = Database::getInstance()
            ->prepare('SELECT * FROM tl_ml_product_archive WHERE id=?')
            ->limit(1)
            ->execute($dc->id)
        ;

        if (!$record->numRows) {
            return;
        }

        if ($record->includeDelete ?? false) {
            $GLOBALS['TL_DCA']['tl_ml_product_archive']['fields']['redirectAfterDelete']['eval']['mandatory'] = true;
        } else {
            unset($GLOBALS['TL_DCA']['tl_ml_product_archive']['fields']['redirectAfterDelete']);
            unset($GLOBALS['TL_DCA']['tl_ml_product_archive']['fields']['groupsCanDeleteOwn']);
            unset($GLOBALS['TL_DCA']['tl_ml_product_archive']['fields']['groupsCanDeleteAll']);
        }
    }

    public function getMemberGroupOptions(): array
    {
        $options = [];
        $groups = MemberGroupModel::findAll();

        if ($groups !== null)
        {
            while ($groups->next()) {
                $options[$groups->id] = $groups->name;
            }

            asort($options);
        }

        return $options;
    }

    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        /** @var BackendUser $user */
        $user = $this->security->getUser();

//        if ($this->security->isGranted(ContaoCorePermissions::USER_CAN_EDIT_FIELDS_OF_TABLE, 'tl_ml_product_archive'))
        if ($user->canEditFieldsOf('tl_ml_product_archive'))
        {
            $anchor = sprintf(
                '<a href="%s&rt=%s" title="%s" %s>%s</a> ',
                Controller::addToUrl("$href&amp;id={$row['id']}"),
                RequestToken::get(),
                StringUtil::specialchars($title),
                $attributes,
                Image::getHtml($icon, $label)
            );

            return $anchor;
        }

        return Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)) . ' ';
    }

    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->hasAccess('create',
            'contao_media_library_bundlep') ? '<a href="'.Controller::addToUrl(
                $href.'&amp;id='.$row['id']
            ).'&rt='.RequestToken::get().'" title="'. StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml(
                $icon,
                $label
            ).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return BackendUser::getInstance()->hasAccess('delete',
            'contao_media_library_bundlep') ? '<a href="'.Controller::addToUrl(
                $href.'&amp;id='.$row['id']
            ).'&rt='.RequestToken::get().'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml(
                $icon,
                $label
            ).'</a> ' : Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
}
