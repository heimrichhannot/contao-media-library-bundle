<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Backend;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;

class ProductArchive
{
    const UPLOAD_FOLDER_MODE_STATIC = 'static';
    const UPLOAD_FOLDER_MODE_USER_HOME_DIR = 'user_home_dir';

    const UPLOAD_FOLDER_MODES = [
        self::UPLOAD_FOLDER_MODE_STATIC,
        self::UPLOAD_FOLDER_MODE_USER_HOME_DIR,
    ];

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
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

        return $imageSizes['image_sizes'];
    }

    /**
     * get fields that have been selected in palette.
     *
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function getFieldsForTags(DataContainer $dc)
    {
        return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
            [
                'dataContainer' => 'tl_ml_product',
                'inputTypes' => ['text', 'textarea', 'select', 'multifileupload', 'checkbox', 'tagsinput'],
            ]
        );
    }

    public function getUploadFolderByProduct(DataContainer $dc)
    {
        return $this->doGetUploadFolder($dc->activeRecord);
    }

    public function getUploadFolderByDownload(DataContainer $dc)
    {
        if (null === ($download = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_ml_download', $dc->id))) {
            return \Contao\Config::get('uploadPath');
        }

        if (null === ($product = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_ml_product', $download->pid))) {
            return \Contao\Config::get('uploadPath');
        }

        return $this->doGetUploadFolder($product);
    }

    /**
     * get the upload folder.
     *
     * @param object $product
     *
     * @return string
     */
    public function doGetUploadFolder($product)
    {
        if (null === ($productArchive = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_ml_product_archive',
                                                                                                              $product->pid))) {
            return Config::get('uploadPath');
        }

        if (null === ($uploadFolder = System::getContainer()->get('huh.utils.file')->getPathFromUuid($productArchive->uploadFolder))) {
            return Config::get('uploadPath');
        }

        $modelUtil = System::getContainer()->get('huh.utils.model');

        switch ($productArchive->uploadFolderMode) {
            case static::UPLOAD_FOLDER_MODE_USER_HOME_DIR:
                if ($productArchive->uploadFolderUserPattern) {
                    $backendUser = $this->framework->createInstance(BackendUser::class);

                    if (null !== ($user = $modelUtil->findModelInstanceByPk('tl_user', $backendUser->id))) {
                        $userPattern = $modelUtil->computeStringPattern(
                            $productArchive->uploadFolderUserPattern,
                            $user,
                            'tl_user'
                        );

                        if ($userPattern) {
                            $uploadFolder .= DIRECTORY_SEPARATOR.System::getContainer()->get('huh.utils.file')->sanitizeFileName($userPattern);
                        }
                    }
                }

                break;
        }

        if ($productArchive->addProductPatternToUploadFolder && $productArchive->uploadFolderProductPattern) {
            $productPattern = $modelUtil->computeStringPattern(
                $productArchive->uploadFolderProductPattern,
                $product,
                'tl_ml_product'
            );

            $uploadFolder .= DIRECTORY_SEPARATOR.System::getContainer()->get('huh.utils.file')->sanitizeFileName($productPattern);
        }

        return $uploadFolder;
    }

    public function checkPermission()
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set root IDs
        if (!is_array($user->contao_media_library_bundles) || empty($user->contao_media_library_bundles)) {
            $root = [0];
        } else {
            $root = $user->contao_media_library_bundles;
        }

        $GLOBALS['TL_DCA']['tl_ml_product_archive']['list']['sorting']['root'] = $root;

        // Check permissions to add archives
        if (!$user->hasAccess('create', 'contao_media_library_bundlep')) {
            $GLOBALS['TL_DCA']['tl_ml_product_archive']['config']['closed'] = true;
        }

        /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $objSession */
        $objSession = \Contao\System::getContainer()->get('session');

        // Check current action
        switch (\Contao\Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(\Contao\Input::get('id'), $root, true)) {
                    /** @var \Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface $sessionBag */
                    $sessionBag = $objSession->getBag('contao_backend');

                    $arrNew = $sessionBag->get('new_records');

                    if (is_array($arrNew['tl_ml_product_archive']) && in_array(\Contao\Input::get('id'), $arrNew['tl_ml_product_archive'], true)) {
                        // Add the permissions on group level
                        if ('custom' != $user->inherit) {
                            $objGroup = $database->execute(
                                'SELECT id, contao_media_library_bundles, contao_media_library_bundlep FROM tl_user_group WHERE id IN('.implode(
                                    ',',
                                    array_map(
                                        'intval',
                                        $user->groups
                                    )
                                ).')'
                            );

                            while ($objGroup->next()) {
                                $arrModulep = \StringUtil::deserialize($objGroup->contao_media_library_bundlep);

                                if (is_array($arrModulep) && in_array('create', $arrModulep, true)) {
                                    $arrModules = \StringUtil::deserialize($objGroup->contao_media_library_bundles, true);
                                    $arrModules[] = \Contao\Input::get('id');

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

                            $arrModulep = \StringUtil::deserialize($user->contao_media_library_bundlep);

                            if (is_array($arrModulep) && in_array('create', $arrModulep, true)) {
                                $arrModules = \StringUtil::deserialize($user->contao_media_library_bundles, true);
                                $arrModules[] = \Contao\Input::get('id');

                                $database->prepare('UPDATE tl_user SET contao_media_library_bundles=? WHERE id=?')->execute(
                                        serialize($arrModules),
                                        $user->id
                                    );
                            }
                        }

                        // Add the new element to the user object
                        $root[] = \Contao\Input::get('id');
                        $user->contao_media_library_bundles = $root;
                    }
                }
            // no break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(\Contao\Input::get('id'), $root, true)
                    || ('delete' == \Contao\Input::get('act')
                        && !$user->hasAccess(
                            'delete',
                            'contao_media_library_bundlep'
                        ))
                ) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to '.\Contao\Input::get('act').' ml_product_archive ID '.\Contao\Input::get('id').'.'
                    );
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $objSession->all();
                if ('deleteAll' == \Contao\Input::get('act') && !$user->hasAccess('delete', 'contao_media_library_bundlep')) {
                    $session['CURRENT']['IDS'] = [];
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $objSession->replace($session);
                break;

            default:
                if (strlen(\Contao\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException(
                        'Not enough permissions to '.\Contao\Input::get('act').' ml_product_archives.'
                    );
                }
                break;
        }
    }

    public function editHeader($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\BackendUser::getInstance()->canEditFieldsOf('tl_ml_product_archive') ? '<a href="'.Controller::addToUrl(
                $href.'&amp;id='.$row['id']
            ).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml(
                $icon,
                $label
            ).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    public function copyArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\BackendUser::getInstance()->hasAccess('create', 'contao_media_library_bundlep') ? '<a href="'.Controller::addToUrl(
                $href.'&amp;id='.$row['id']
            ).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml(
                $icon,
                $label
            ).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }

    public function deleteArchive($row, $href, $label, $title, $icon, $attributes)
    {
        return \Contao\BackendUser::getInstance()->hasAccess('delete', 'contao_media_library_bundlep') ? '<a href="'.Controller::addToUrl(
                $href.'&amp;id='.$row['id']
            ).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml(
                $icon,
                $label
            ).'</a> ' : \Image::getHtml(preg_replace('/\.svg$/i', '_.svg', $icon)).' ';
    }
}
