<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 13.03.18
 * Time: 11:59
 */

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], count($GLOBALS['BE_MOD']['content']), [
    'media-library' => [
        'tables' => ['tl_ml_product_archive', 'tl_ml_product', 'tl_ml_download'],
    ],
]);

$GLOBALS['TL_HOOKS']['getPageLayout'][] = ['huh.media_library.ajax_manager', 'ajaxActions'];

/**
 * Assets
 */
if(\Contao\System::getContainer()->get('huh.utils.container')->isFrontend() && !class_exists('HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension'))
{
    $GLOBALS['TL_JAVASCRIPT']['contao-media-library-bundle'] = 'bundles/heimrichhannotcontaomedialibrary/js/contao-media-library-bundle.js|static';
}

if(\Contao\System::getContainer()->get('huh.utils.container')->isBackend())
{
    $GLOBALS['TL_CSS']['contao-media-library-bundle-be'] = 'bundles/heimrichhannotcontaomedialibrary/css/contao-media-library-bundle-be.css';
}

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_ml_product_archive'] = 'HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel';
$GLOBALS['TL_MODELS']['tl_ml_product']         = 'HeimrichHannot\MediaLibraryBundle\Model\ProductModel';
$GLOBALS['TL_MODELS']['tl_ml_download']        = 'HeimrichHannot\MediaLibraryBundle\Model\DownloadModel';

$GLOBALS['AJAX'][\HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_XHR_GROUP] = [
    \HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS => [
        'arguments' => [
            \HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_ARGUMENTS_OPTIONS
        ],
        'optional' => []
    ]
];

/**
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'contao_media_library_bundles';
$GLOBALS['TL_PERMISSIONS'][] = 'contao_media_library_bundlep';
