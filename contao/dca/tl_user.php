<?php

$dca = &$GLOBALS['TL_DCA']['tl_user'];

/**
 * Palettes
 */
$dca['palettes']['extend'] = str_replace('fop;', 'fop;{contao_media_library_bundle_legend},contao_media_library_bundles,contao_media_library_bundlep;', $dca['palettes']['extend']);
$dca['palettes']['custom'] = str_replace('fop;', 'fop;{contao_media_library_bundle_legend},contao_media_library_bundles,contao_media_library_bundlep;', $dca['palettes']['custom']);

/**
 * Fields
 */
$dca['fields']['contao_media_library_bundles'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_user']['contao_media_library_bundles'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'foreignKey' => 'tl_ml_product_archive.title',
    'eval'       => ['multiple' => true],
    'sql'        => "blob NULL"
];

$dca['fields']['contao_media_library_bundlep'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_user']['contao_media_library_bundlep'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['create', 'delete'],
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval'      => ['multiple' => true],
    'sql'       => "blob NULL"
];
