<?php

use HeimrichHannot\MediaLibraryBundle\Contao\BackendModule;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

/*
 * Backend modules
 */
$GLOBALS['BE_MOD'][BackendModule::CATEGORY][BackendModule::NAME] = [
    'tables' => BackendModule::getTables(),
];

// todo(@ericges): remove this hook once the ajax manager is removed
$GLOBALS['TL_HOOKS']['getPageLayout'][] = ['huh.media_library.ajax_manager', 'ajaxActions'];

/*
 * Assets
 */
// if (\Contao\System::getContainer()->get('huh.utils.container')->isFrontend() && !class_exists(\HeimrichHannot\EncoreBundle\DependencyInjection\EncoreExtension::class)) {
//     $GLOBALS['TL_JAVASCRIPT']['contao-media-library-bundle'] = 'bundles/heimrichhannotmedialibrary/js/contao-media-library-bundle.js|static';
// }

/*
 * Models
 */
$GLOBALS['TL_MODELS'][ArchiveModel::getTable()] = ArchiveModel::class;
$GLOBALS['TL_MODELS'][DownloadModel::getTable()] = DownloadModel::class;
$GLOBALS['TL_MODELS'][ItemModel::getTable()] = ItemModel::class;

// $GLOBALS['AJAX'][\HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_XHR_GROUP] = [
//     \HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS => [
//         'arguments' => [
//             \HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_ARGUMENTS_OPTIONS,
//         ],
//         'optional' => [],
//     ],
// ];

/*
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'contao_media_library_bundles';
$GLOBALS['TL_PERMISSIONS'][] = 'contao_media_library_bundlep';
