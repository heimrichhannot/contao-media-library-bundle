<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$dca = &$GLOBALS['TL_DCA']['tl_form'];

$dca['fields']['ml_archive'] = [
    'inputType' => 'select',
    'eval' => [
        'tl_class' => 'w50',
        'includeBlankOption' => true,
        'chosen' => true,
        'mandatory' => true,
    ],
    'sql' => "int(10) unsigned NOT NULL default '0'",
];
$dca['fields']['ml_publish'] = [
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'w50 clr',
    ],
    'sql' => "char(1) NOT NULL default ''",
];
