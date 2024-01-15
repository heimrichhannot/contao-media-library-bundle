<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\MediaLibraryBundle\DataContainer\ProductContainer;

$GLOBALS['TL_DCA']['tl_ml_product'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_ml_product_archive',
        'ctable' => ['tl_ml_download'],
        'enableVersioning' => true,
        'oncreate_callback' => [
            [ProductContainer::class, 'setType'],
        ],
        'onload_callback' => [
            [ProductContainer::class, 'addAdditionalFields'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
            [ProductContainer::class, 'generateDownloadItems'],
            [ProductContainer::class, 'setCopyright'],
            [ProductContainer::class, 'updateTagAssociations'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'ondelete_callback' => [
            [ProductContainer::class, 'deleteDownloads'],
            [ProductContainer::class, 'deleteTagAssociations'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 4,
            'fields' => ['title'],
            'headerFields' => ['title', 'tstamp'],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => [ProductContainer::class, 'listChildren'],
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
                'icon' => 'edit.svg',
            ],
            'downloads' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['downloads'],
                'href' => 'table=tl_ml_download',
                'icon' => 'bundles/heimrichhannotcontaomedialibrary/img/icon-download.png',
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '')
                    .'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['toggle'],
                'icon' => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [ProductContainer::class, 'toggleIcon'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'addAdditionalFiles', 'protected', 'published'],
        'default' => 'type',
    ProductContainer::TYPE_FILE => '{general_legend},title,alias;{product_legend},file,copyright,doNotCreateDownloadItems,text;{additional_fields_legend};{protected_legend},protected;{publish_legend},published;',
        ProductContainer::TYPE_VIDEO => '{general_legend},title,alias;{product_legend},file,videoPosterImage,copyright,doNotCreateDownloadItems,text;{additional_fields_legend};{protected_legend},protected;{publish_legend},published;',
        ProductContainer::TYPE_IMAGE => '{general_legend},title,alias;{product_legend},file,copyright,doNotCreateDownloadItems,text;{additional_fields_legend};{protected_legend},protected;{publish_legend},published;',
    ],
    'subpalettes' => [
        'addAdditionalFiles' => 'additionalFiles',
        'published' => 'start,stop',
        'protected' => 'groups',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['pid'],
            'foreignKey' => 'tl_ml_product_archive.title',
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['alias'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'doNotCopy' => true],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['type'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ProductContainer::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_ml_product']['reference'],
            'eval' => [
                'tl_class' => 'w50',
                'mandatory' => true,
                'includeBlankOption' => true,
                'submitOnChange' => true,
            ],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'file' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['file'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'tl_class' => 'clr',
                'filesOnly' => true,
                'fieldType' => 'radio',
                'mandatory' => true,
                'doNotCopy' => true,
                'unique' => true,
            ],
            'sql' => 'binary(16) NULL',
        ],
        'addAdditionalFiles' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['addAdditionalFiles'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 clr',
                'doNotCopy' => true,
                'isAdditionalField' => true,
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'additionalFiles' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['additionalFiles'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'tl_class' => 'w50 autoheight clr',
                'multiple' => true,
                'fieldType' => 'checkbox',
                'orderField' => 'additionalFilesOrder',
                'filesOnly' => true,
                'mandatory' => true,
                'doNotCopy' => true,
            ],
            'sql' => 'blob NULL',
        ],
        'additionalFilesOrder' => [
            'sql' => 'blob NULL',
            'eval' => [
                'doNotCopy' => true,
            ],
        ],
        'videoPosterImage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['videoPosterImage'],
            'exclude' => true,
            'inputType' => 'fileTree',
            'eval' => [
                'tl_class' => 'clr',
                'filesOnly' => true,
                'fieldType' => 'radio',
                'extensions' => Config::get('validImageTypes'),
                'mandatory' => true,
            ],
            'sql' => 'blob NULL',
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
            'sql' => 'text NULL',
        ],
        'tags' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['tags'],
            'exclude' => true,
            'inputType' => 'cfgTags',
            'eval' => [
                'tagsManager' => 'huh_media_library_product',
                'tl_class' => 'clr',
                'isAdditionalField' => true,
            ],
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['start'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['stop'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'protected' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['protected'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['groups'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true],
            'sql' => 'blob NULL',
        ],
    ],
];

\HeimrichHannot\CategoriesBundle\Backend\Category::addMultipleCategoriesFieldToDca(
    'tl_ml_product',
    'categories',
    [
        'addPrimaryCategory' => false,
        'mandatory' => false,
        'parentsUnselectable' => true,
        'isAdditionalField' => true,
    ]
);

System::getContainer()->get('huh.utils.dca')->addOverridableFields(
    ['imageSizes'],
    'tl_ml_product_archive',
    'tl_ml_product'
);

\Contao\System::getContainer()->get(\HeimrichHannot\UtilsBundle\Dca\DcaUtil::class)->addAuthorFieldAndCallback('tl_ml_product');
