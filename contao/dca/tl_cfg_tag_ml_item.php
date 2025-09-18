<?php

$GLOBALS['TL_DCA']['tl_cfg_tag_ml_item'] = [
    'config' => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'notCopyable'      => true,
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
    ],
];
