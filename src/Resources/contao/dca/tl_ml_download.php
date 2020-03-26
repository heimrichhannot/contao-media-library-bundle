<?php

$GLOBALS['TL_DCA']['tl_ml_download'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_ml_product',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['huh.media_library.backend.download', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id'                       => 'primary',
                'pid,start,stop,published' => 'index'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['title'],
            'panelLayout' => 'filter;sort,search,limit',

        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_ml_download']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_ml_download']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['huh.media_library.backend.download', 'toggleIcon']
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['published'],
        'default'      => '{general_legend},title,imageSize,file;{publish_legend},published;'
    ],
    'subpalettes' => [
        'published' => 'start,stop'
    ],
    'fields'      => [
        'id'           => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'          => [
            'foreignKey' => 'tl_ml_product.id',
            'exclude'   => true,
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager']
        ],
        'tstamp'       => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['tstamp'],
            'exclude'   => true,
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'    => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'file' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['file'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'tl_class'           => 'clr',
                'filesOnly'          => true,
                'fieldType'          => 'radio',
                'mandatory'          => true
            ],
            'sql'       => "blob NULL",
        ],
        'imageSize'    => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_download']['imageSize'],
            'exclude'          => true,
            'inputType'        => 'select',
            'eval'             => ['tl_class' => 'w50', 'readonly' => true, 'includeBlankOption' => true],
            'options_callback' => ['huh.media_library.backend.product_archive', 'getImageSizes'],
            'sql'              => "int(10) unsigned NOT NULL default '0'"
        ],
        'published'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'start'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'stop'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ]
    ]
];

$containerUtil = \Contao\System::getContainer()->get('huh.utils.dca');
$containerUtil->addAuthorFieldAndCallback('tl_ml_download');