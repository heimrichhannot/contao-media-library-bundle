<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\System;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ProductArchiveContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ProductContainer;

System::loadLanguageFile('tl_ml_product');

$GLOBALS['TL_DCA']['tl_ml_product_archive'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable' => ['tl_ml_product'],
        'enableVersioning' => true,
        'onload_callback' => [
            [ProductArchiveContainer::class, 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'oncopy_callback' => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],
    'list' => [
        'label' => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'sorting' => [
            'mode' => 2,
            'fields' => ['title'],
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
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
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['edit'],
                'href' => 'table=tl_ml_product',
                'icon' => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
                'button_callback' => [ProductArchiveContainer::class, 'editHeader'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
                'button_callback' => [ProductArchiveContainer::class, 'copyArchive'],
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '')
                    .'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => [ProductArchiveContainer::class, 'deleteArchive'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'protected', 'useExifDataForTags', 'allowEdit', 'includeDelete'],
        'default' => '{general_legend},title,jumpTo;{config_legend},type,additionalFields,keepProductTitleForDownloadItems;{edit_legend},allowEdit,includeDelete;{protected_legend},protected;',
    ],
    'subpalettes' => [
        'type_'. ProductContainer::TYPE_IMAGE => 'imageSizes',
        'allowEdit' => 'editJumpTo',
        'includeDelete' => 'redirectAfterDelete,groupsCanDeleteOwn,groupsCanDeleteAll',
        'protected' => 'groups',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['tstamp'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        // general
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['type'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ProductContainer::TYPES,
            'reference' => &$GLOBALS['TL_LANG']['tl_ml_product']['reference'],
            'eval' => ['tl_class' => 'w50', 'mandatory' => true, 'includeBlankOption' => true, 'submitOnChange' => true],
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'jumpTo' => [
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['mandatory' => true, 'fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => ['type' => 'hasOne', 'load' => 'lazy']
        ],
        'additionalFields' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['additionalFields'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => function (Contao\DataContainer $dc) {
                return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
                    [
                        'dataContainer' => 'tl_ml_product',
                        'evalConditions' => [
                            'isAdditionalField' => true,
                        ],
                    ]
                );
            },
            'eval' => ['tl_class' => 'clr w50 autoheight', 'multiple' => true],
            'sql' => 'blob NULL',
        ],
        // image
        'imageSizes' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['imageSizes'],
            'exclude' => true,
            'flag' => 1,
            'inputType' => 'checkboxWizard',
            'options_callback' => [ProductArchiveContainer::class, 'getImageSizes'],
            'eval' => ['includeBlankOption' => true, 'multiple' => true, 'tl_class' => 'clr w50 autoheight'],
            'sql' => 'blob NULL',
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
        'keepProductTitleForDownloadItems' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['keepProductTitleForDownloadItems'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'eval' => ['tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'allowEdit' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'w50 clr',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'editJumpTo' => [
            'exclude'                 => true,
            'inputType'               => 'pageTree',
            'foreignKey'              => 'tl_page.title',
            'eval'                    => array('fieldType'=>'radio'),
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => array('type'=>'hasOne', 'load'=>'lazy')
        ],
        'includeDelete' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'eval' => [
                'tl_class' => 'clr',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'redirectAfterDelete' => [
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.id',
            'eval' => [
                'fieldType' => 'radio',
                'tl_class' => 'clr',
                'mandatory' => true,
            ],
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => [
                'type' => 'hasOne',
                'load' => 'lazy'
            ]
        ],
        'groupsCanDeleteAll' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkboxWizard',
            'foreignKey'              => 'tl_user_group.id',
            'eval'                    => [
                'multiple' => true,
                'tl_class' => 'w50'
            ],
            'sql'                     => "blob NULL",
            'relation'                => [
                'type' => 'belongsToMany',
                'load'=>'lazy'
            ],
            'options_callback' => [ProductArchiveContainer::class, 'getMemberGroupOptions']
        ],
        'groupsCanDeleteOwn' => [
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkboxWizard',
            'foreignKey'              => 'tl_user_group.id',
            'eval'                    => [
                'multiple' => true,
                'tl_class' => 'w50'
            ],
            'sql'                     => "blob NULL",
            'relation'                => [
                'type' => 'belongsToMany',
                'load'=>'lazy'
            ],
            'options_callback' => [ProductArchiveContainer::class, 'getMemberGroupOptions']
        ]
    ],
];
