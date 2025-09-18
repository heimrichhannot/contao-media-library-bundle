<?php

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;

$archiveTable = ArchiveModel::getTable();

$dca = &$GLOBALS['TL_DCA']['tl_user_group'];

/**
 * Palettes
 */
$dca['palettes']['default'] = str_replace('fop;', 'fop;{contao_media_library_bundle_legend},contao_media_library_bundles,contao_media_library_bundlep;', $dca['palettes']['default']);

/**
 * Fields
 */
$dca['fields']['contao_media_library_bundles'] = [
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => "{$archiveTable}.title",
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$dca['fields']['contao_media_library_bundlep'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];
