<?php

use Contao\DC_Table;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ProductArchiveContainer;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\MediaLibraryBundle\Util\Str;

$table = ArchiveModel::getTable();
$itemTable = ItemModel::getTable();

$dca = &$GLOBALS['TL_DCA'][$table];

$dca['palettes'] = [
    '__selector__' => ['type', 'protected', 'useExifDataForTags', 'enableCreate', 'enableEdit', 'enableDelete'],
    '__prefix__' => '{general_legend},title,type,jumpTo;{advanced_legend},additionalFields,keepProductTitleForDownloadItems;',
    '__suffix__' => '{edit_legend},enableCreate,enableEdit,enableDelete;',
];

$dca['palettes']['default'] = Str::mergePalettes($dca['palettes']['__prefix__'], $dca['palettes']['__suffix__']);

$dca['subpalettes'] = [
    'enableCreate' => 'createJumpTo',
    'enableEdit' => 'editJumpTo',
    'enableDelete' => 'deleteJumpTo',
];

$contao5 = !\defined('VERSION');

$dca['config'] = [
    'dataContainer' => DC_Table::class,
    'ctable' => [$itemTable],
    'enableVersioning' => true,
    'sql' => [
        'keys' => [
            'id' => 'primary',
        ],
    ],
];

$dca['list'] = [
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
        $contao5 ? 'children' : 'edit' => [
            'href' => "table=$itemTable",
            'icon' => $contao5 ? 'children.svg' : 'edit.svg',
        ],
        $contao5 ? 'edit' : 'editheader' => [
            'href' => 'act=edit',
            'icon' => $contao5 ? 'edit.svg' : 'header.svg',
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
];

$dca['fields'] = [
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
        'eval' => [
            'tl_class' => 'w50',
            'mandatory' => true,
            'includeBlankOption' => true,
            'submitOnChange' => true,
        ],
        'sql' => "varchar(128) NOT NULL default ''",
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
        'eval' => ['tl_class' => 'clr w50 autoheight', 'multiple' => true],
        'sql' => 'blob NULL',
    ],
    // image
    'imageSizes' => [
        'exclude' => true,
        'flag' => 1,
        'inputType' => 'checkboxWizard',
        'eval' => ['includeBlankOption' => true, 'multiple' => true, 'tl_class' => 'clr w50 autoheight'],
        'sql' => 'blob NULL',
    ],
    'protected' => [
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['submitOnChange' => true],
        'sql' => "char(1) NOT NULL default ''",
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
];
