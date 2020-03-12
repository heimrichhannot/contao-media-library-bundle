<?php

\Contao\System::loadLanguageFile('tl_ml_product');

$GLOBALS['TL_DCA']['tl_ml_product_archive'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ctable'            => 'tl_ml_product',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['huh.media_library.backend.product_archive', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded']
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
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['edit'],
                'href'  => 'table=tl_ml_product',
                'icon'  => 'edit.svg',
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
                'icon'  => 'copy.svg',
                'button_callback' => ['huh.media_library.backend.product_archive', 'copyArchive'],
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\'))return false;Backend.getScrollOffset()"',
                'button_callback' => ['huh.media_library.backend.product_archive', 'deleteArchive'],
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['type', 'uploadFolderMode', 'addProductPatternToUploadFolder', 'protected', 'published','useExifDataForTags'],
        'default'      => '{general_legend},title;{config_legend},type,additionalFields,keepProductTitleForDownloadItems;{protected_legend},protected;{publish_legend},published;'
    ],
    'subpalettes' => [
        'type_' . \HeimrichHannot\MediaLibraryBundle\Backend\Product::TYPE_IMAGE                      => 'uploadFolderMode,imageSizes',
        'type_' . \HeimrichHannot\MediaLibraryBundle\Backend\Product::TYPE_FILE                       => 'uploadFolderMode',
        'type_' . \HeimrichHannot\MediaLibraryBundle\Backend\Product::TYPE_VIDEO                      => 'uploadFolderMode',
        'uploadFolderMode_'
        . \HeimrichHannot\MediaLibraryBundle\Backend\ProductArchive::UPLOAD_FOLDER_MODE_STATIC        => 'uploadFolder,addProductPatternToUploadFolder',
        'uploadFolderMode_'
        . \HeimrichHannot\MediaLibraryBundle\Backend\ProductArchive::UPLOAD_FOLDER_MODE_USER_HOME_DIR => 'uploadFolder,uploadFolderUserPattern,addProductPatternToUploadFolder',
        'addProductPatternToUploadFolder'                                                             => 'uploadFolderProductPattern',
        'protected'                                                                                   => 'groups',
        'published'                                                                                   => 'start,stop',
    ],
    'fields'      => [
        'id'                              => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'                          => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'                       => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        // general
        'title'                           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'type'                            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['type'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\MediaLibraryBundle\Backend\Product::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_ml_product']['reference'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'additionalFields'                => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['additionalFields'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => function (\Contao\DataContainer $dc) {
                return \Contao\System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
                    [
                        'dataContainer'  => 'tl_ml_product',
                        'evalConditions' => [
                            'isAdditionalField' => true
                        ]
                    ]
                );
            },
            'eval'             => ['tl_class' => 'w50 autoheight', 'multiple' => true],
            'sql'              => "blob NULL"
        ],
        // image
        'imageSizes'                      => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['imageSizes'],
            'exclude'          => true,
            'flag'             => 1,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.media_library.backend.product_archive', 'getImageSizes'],
            'eval'             => ['includeBlankOption' => true, 'multiple' => true, 'tl_class' => 'w50 autoheight'],
            'sql'              => "blob NULL"
        ],
        // config
        'uploadFolderMode'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['uploadFolderMode'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => \HeimrichHannot\MediaLibraryBundle\Backend\ProductArchive::UPLOAD_FOLDER_MODES,
            'reference' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['reference'],
            'eval'      => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql'       => "varchar(64) NOT NULL default ''"
        ],
        'uploadFolder'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['uploadFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'tl_class' => 'w50 autoheight', 'mandatory' => true],
            'sql'       => "binary(16) NULL",
        ],
        'uploadFolderUserPattern'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['uploadFolderUserPattern'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'addProductPatternToUploadFolder' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['addProductPatternToUploadFolder'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'w50', 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'uploadFolderProductPattern'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['uploadFolderProductPattern'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50 clr', 'mandatory' => true],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'protected'                       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['protected'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'groups'                          => [
            'label'      => &$GLOBALS['TL_LANG']['tl_page']['groups'],
            'exclude'    => true,
            'inputType'  => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval'       => ['mandatory' => true, 'multiple' => true],
            'sql'        => "blob NULL"
        ],
        'published'                       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'default'   => true,
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'start'                           => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'stop'                            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'keepProductTitleForDownloadItems' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['keepProductTitleForDownloadItems'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'default'   => true,
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
    ]
];