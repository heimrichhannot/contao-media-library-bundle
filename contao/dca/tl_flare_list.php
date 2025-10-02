<?php

$dca = &$GLOBALS['TL_DCA']['tl_flare_list'];

$dca['fields']['ml_archive'] = [
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_ml_archive.title',
    'eval' => ['mandatory' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
