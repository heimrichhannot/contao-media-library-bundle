<?php

$GLOBALS['TL_DCA']['tl_ml_product'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_ml_product_archive',
        'ctable' => 'tl_ml_download',
        'enableVersioning' => true,
        'onload_callback' => [
            ['huh.media_library.backend.product', 'setType'],
            ['huh.media_library.backend.product', 'addAdditionalFields'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
            ['huh.media_library.backend.product', 'generateDownloadItems'],
            ['huh.media_library.backend.product', 'generateAlias'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
            ['huh.media_library.backend.product', 'copyFile'],
        ],
        'ondelete_callback' => [
            ['huh.media_library.backend.product', 'deleteDownloads']
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index'
            ]
        ]
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting' => [
            'mode' => 4,
            'fields' => ['title'],
            'headerFields' => ['title', 'tstamp'],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => ['huh.media_library.backend.product', 'listChildren']
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.svg'
            ],
            'downloads' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['downloads'],
                'href' => 'table=tl_ml_download',
                'icon' => 'bundles/heimrichhannotcontaomedialibrary/img/icon-download.png'
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg'
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['toggle'],
                'icon' => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['huh.media_library.backend.product', 'toggleIcon']
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg'
            ],
        ]
    ],
    'palettes' => [
        '__selector__' => ['type', 'published'],
        \HeimrichHannot\MediaLibraryBundle\Backend\Product::TYPE_FILE => '{general_legend},title,alias;{product_legend},file,copyright,doNotCreateDownloadItems,text,tags;{additional_fields_legend};{publish_legend},published;',
        \HeimrichHannot\MediaLibraryBundle\Backend\Product::TYPE_IMAGE => '{general_legend},title,alias;{product_legend},file,copyright,doNotCreateDownloadItems,text,tags,overrideImageSizes;{additional_fields_legend};{publish_legend},published;'
    ],
    'subpalettes' => [
        'published' => 'start,stop'
    ],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['pid'],
            'foreignKey' => 'tl_ml_product_archive.title',
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'eager']
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['alias'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['type'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => \HeimrichHannot\MediaLibraryBundle\Backend\Product::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_ml_product']['reference'],
            'eval' => [
                'tl_class' => 'w50',
                'mandatory' => true,
                'includeBlankOption' => true,
                'submitOnChange' => true
            ],
            'sql' => "varchar(64) NOT NULL default ''"
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''"
        ],
        'file' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['file'],
            'exclude' => true,
            'inputType' => 'multifileupload',
            'eval' => [
                'tl_class' => 'long clr',
                'extensions' => \Config::get('validImageTypes'),
                'filesOnly' => true,
                'fieldType' => 'radio',
                'maxImageWidth' => \Config::get('gdMaxImgWidth'),
                'maxImageHeight' => \Config::get('gdMaxImgHeight'),
                'uploadFolder' => ['huh.media_library.backend.product_archive', 'getUploadFolderByProduct'],
                'addRemoveLinks' => true,
                'maxFiles' => 1,
                'maxUploadSize' => \Config::get('maxFileSize'),
                'mandatory' => true,
            ],
            'sql' => "blob NULL",
        ],
        'doNotCreateDownloadItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['doNotCreateDownloadItems'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'text' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['text'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => ['tl_class' => 'clr', 'rte' => 'tinyMCE'],
            'sql' => "text NULL",
        ],
        'tags' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['tags'],
            'exclude' => true,
            'inputType' => 'cfgTags',
            'eval' => [
                'tagsManager' => 'huh.media_library.tags.product',
                'tl_class' => 'clr'
            ],
        ],
        'copyright' => [
            'label' => $GLOBALS['TL_LANG']['tl_ml_product']['copyright'],
            'inputType' => 'tagsinput',
            'exclude' => true,
            'options_callback' => ['HeimrichHannot\FileCredit\Backend\FileCredit', 'getFileCreditOptions'],
            'eval' => [
                'maxlength' => 255,
                'decodeEntities' => true,
                'tl_class' => 'long clr',
                'freeInput' => true,
                'multiple' => true,
                'doNotSaveEmpty' => true
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_files'],
            'load_callback' => [
                ['huh.media_library.backend.product', 'getCopyright'],
            ],
            'save_callback' => [
                ['huh.media_library.backend.product', 'setCopyright'],
            ],
            'sql' => "blob NULL"
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''"
        ],
        'start' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['start'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''"
        ],
        'stop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['stop'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''"
        ]
    ]
];

System::getContainer()->get('huh.utils.dca')->addOverridableFields(
    ['imageSizes'],
    'tl_ml_product_archive',
    'tl_ml_product'
);

//$GLOBALS['TL_DCA']['tl_ml_product']['fields']['copyright']['eval']['mandatory'] = true;