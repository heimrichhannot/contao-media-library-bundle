<?php

use Contao\DC_Table;
use Contao\System;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ProductArchiveContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ProductContainer;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

$table = ArchiveModel::getTable();
$itemTable = ItemModel::getTable();

$GLOBALS['TL_DCA'][$table] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => [$itemTable],
        'enableVersioning' => true,
        'onload_callback' => [
            [ProductArchiveContainer::class, 'checkPermission'],
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
                'label' => &$GLOBALS['TL_LANG'][$table]['edit'],
                'href' => "table=$itemTable",
                'icon' => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG'][$table]['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.svg',
                'button_callback' => [ProductArchiveContainer::class, 'editHeader'],
            ],
            'copy' => [
                'label' => &$GLOBALS['TL_LANG'][$table]['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.svg',
                'button_callback' => [ProductArchiveContainer::class, 'copyArchive'],
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG'][$table]['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '')
                    .'\'))return false;Backend.getScrollOffset()"',
                'button_callback' => [ProductArchiveContainer::class, 'deleteArchive'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG'][$table]['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['type', 'protected', 'useExifDataForTags', 'enableCreate', 'enableEdit', 'enableDelete'],
        'default' => '
            {general_legend},title,jumpTo;
            {config_legend},type,additionalFields,keepProductTitleForDownloadItems;
            {edit_legend},enableCreate,enableEdit,enableDelete;
        ',
    ],
    'subpalettes' => [
        'type_'. ProductContainer::TYPE_IMAGE => 'imageSizes',
        'enableCreate' => 'createJumpTo',
        'enableEdit' => 'editJumpTo',
        'enableDelete' => 'deleteJumpTo',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
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
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'type' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => ProductContainer::TYPES,
            'reference' => &$GLOBALS['TL_LANG'][$itemTable]['reference'],
            'eval' => [
                'tl_class' => 'w50',
                'mandatory' => true,
                'includeBlankOption' => true,
                'submitOnChange' => true,
            ],
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
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => function (Contao\DataContainer $dc) use ($itemTable) {
                return System::getContainer()->get('huh.utils.choice.field')->getCachedChoices(
                    [
                        'dataContainer' => $itemTable,
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
            'exclude' => true,
            'flag' => 1,
            'inputType' => 'checkboxWizard',
            'options_callback' => [ProductArchiveContainer::class, 'getImageSizes'],
            'eval' => ['includeBlankOption' => true, 'multiple' => true, 'tl_class' => 'clr w50 autoheight'],
            'sql' => 'blob NULL',
        ],
        'protected' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'groups' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'foreignKey' => 'tl_member_group.name',
            'eval' => ['mandatory' => true, 'multiple' => true],
            'sql' => 'blob NULL',
        ],
        'keepProductTitleForDownloadItems' => [
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'eval' => ['tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'enableCreate' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'enableEdit' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'enableDelete' => [
            'exclude' => true,
            'inputType' => 'checkbox',
            'default' => true,
            'eval' => [
                'tl_class' => 'clr',
                'submitOnChange' => true,
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'createJumpTo' => [
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio'],
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => [
                'type' => 'hasOne',
                'load' => 'lazy'
            ],
        ],
        'editJumpTo' => [
            'exclude' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['fieldType' => 'radio'],
            'sql' => "int(10) unsigned NOT NULL default 0",
            'relation' => [
                'type' => 'hasOne',
                'load' => 'lazy'
            ],
        ],
        'deleteJumpTo' => [
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
    ],
];
