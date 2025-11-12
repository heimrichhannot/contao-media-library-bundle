<?php

use HeimrichHannot\MediaLibraryBundle\Contao\BackendModule;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

/*
 * Backend modules
 */
$GLOBALS['BE_MOD'][BackendModule::CATEGORY][BackendModule::NAME] = [
    'tables' => BackendModule::getTables(),
];

/*
 * Models
 */
$GLOBALS['TL_MODELS'][ArchiveModel::getTable()] = ArchiveModel::class;
$GLOBALS['TL_MODELS'][ItemModel::getTable()] = ItemModel::class;

/*
 * Permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'contao_media_library_bundles';
$GLOBALS['TL_PERMISSIONS'][] = 'contao_media_library_bundlep';
