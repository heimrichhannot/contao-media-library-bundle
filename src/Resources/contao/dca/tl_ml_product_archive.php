<?php

\Contao\Controller::loadDataContainer('tl_ml_product');

$GLOBALS['TL_DCA']['tl_ml_product_archive'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ctable'            => 'tl_ml_product',
        'enableVersioning'  => true,
        'onload_callback' => [
            ['huh.media_library.backend.product_archive', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'         => 2,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit'
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
            'edit'       => [
                'label'           => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['edit'],
                'href'            => 'table=tl_ml_product',
                'icon'            => 'edit.svg',
            ],
            'editheader' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['editheader'],
                'href'            => 'act=edit',
                'icon'            => 'header.svg',
                'button_callback' => ['huh.media_library.backend.product_archive', 'editHeader'],
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['createTagsFromValues'],
        'default'      => '{general_legend},title,palette,imageSize,createTagsFromValues,uploadFolder;'
    ],
    'subpalettes' => [
        'createTagsFromValues' => 'fieldsForTags'
    ],
    'fields'      => [
        'id'                   => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'               => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'            => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'palette'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['palette'],
            'exclude'          => true,
            'flag'             => 1,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.media_library.backend.product_archive', 'getPaletteFields'],
            'eval'             => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql'              => "blob NULL"
        ],
        'imageSize'            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['imageSize'],
            'exclude'          => true,
            'flag'             => 1,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.media_library.backend.product_archive', 'getImageSizes'],
            'eval'             => ['includeBlankOption' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql'              => "blob NULL"
        ],
        'uploadFolder'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['uploadFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql'       => "binary(16) NULL",
        ],
        'createTagsFromValues' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['createTagsFromValues'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'fieldsForTags'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['fieldsForTags'],
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.media_library.backend.product_archive', 'getFieldsForTag'],
            'eval'             => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql'              => "blob NULL"
        ],
    ]
];