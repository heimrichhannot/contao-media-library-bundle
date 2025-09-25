<?php

use Contao\DC_Table;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\UtilsBundle\Dca\AuthorField;
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

$table = DownloadModel::getTable();
$itemTable = ItemModel::getTable();

AuthorField::register($table);
DateAddedField::register($table);

$dca = &$GLOBALS['TL_DCA'][$table];

$dca['palettes'] = [
    '__selector__' => ['published'],
    'default' => '{general_legend},title,authorType,author,isAdditional,file;{publish_legend},published;',
];

$dca['subpalettes'] = [
    'published' => 'start,stop',
];

$dca['config'] = [
    'dataContainer' => DC_Table::class,
    'ptable' => $itemTable,
    'enableVersioning' => true,
    'onload_callback' => [
        [\HeimrichHannot\MediaLibraryBundle\DataContainer\DownloadContainer::class, 'checkPermission'],
    ],
    'sql' => [
        'keys' => [
            'id' => 'primary',
            'pid,start,stop,published' => 'index',
        ],
    ],
];

$dca['list'] = [
    'label' => [
        'fields' => ['title'],
        'format' => '%s',
    ],
    'sorting' => [
        'mode' => 4,
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
            'href' => 'act=edit',
            'icon' => 'edit.gif',
        ],
        'delete' => [
            'label' => &$GLOBALS['TL_LANG'][$table]['delete'],
            'href' => 'act=delete',
            'icon' => 'delete.gif',
            'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '')
                .'\'))return false;Backend.getScrollOffset()"',
        ],
        'toggle' => [
            'label' => &$GLOBALS['TL_LANG'][$table]['toggle'],
            'icon' => 'visible.gif',
            'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
            'button_callback' => [\HeimrichHannot\MediaLibraryBundle\DataContainer\DownloadContainer::class, 'toggleIcon'],
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
    'pid' => [
        'foreignKey' => "$itemTable.id",
        'exclude' => true,
        'sql' => "int(10) unsigned NOT NULL default '0'",
        'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
    ],
    'originalDownload' => [
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'tstamp' => [
        'exclude' => true,
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'dateAdded' => [
        'sorting' => true,
        'flag' => 6,
        'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
        'sql' => "int(10) unsigned NOT NULL default '0'",
    ],
    'title' => [
        'exclude' => true,
        'search' => true,
        'sorting' => true,
        'flag' => 1,
        'inputType' => 'text',
        'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
        'sql' => "varchar(255) NOT NULL default ''",
    ],
    'file' => [
        'exclude' => true,
        'inputType' => 'fileTree',
        'eval' => [
            'tl_class' => 'long autoheight clr',
            'filesOnly' => true,
            'fieldType' => 'radio',
            'mandatory' => true,
        ],
        'sql' => 'blob NULL',
    ],
    'isAdditional' => [
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['tl_class' => 'w50', 'disabled' => true],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'published' => [
        'exclude' => true,
        'filter' => true,
        'inputType' => 'checkbox',
        'eval' => ['doNotCopy' => true, 'submitOnChange' => true],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'start' => [
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        'sql' => "varchar(10) NOT NULL default ''",
    ],
    'stop' => [
        'exclude' => true,
        'inputType' => 'text',
        'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
        'sql' => "varchar(10) NOT NULL default ''",
    ],
];
