<?php

use Contao\DC_Table;
use HeimrichHannot\CategoriesBundle\Backend\Category;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ProductContainer;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\MediaLibraryBundle\Util\Str;
use HeimrichHannot\UtilsBundle\Dca\AliasField;
use HeimrichHannot\UtilsBundle\Dca\AuthorField;
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

$table = ItemModel::getTable();
$archiveTable = ArchiveModel::getTable();

AuthorField::register($table)->setType(AuthorField::TYPE_MEMBER);
DateAddedField::register($table);
AliasField::register($table);

$dca = &$GLOBALS['TL_DCA'][$table];

$dca['palettes'] = [
    '__selector__' => ['type', 'addAdditionalFiles', 'protected'],
    '__prefix__' => '{general_legend},title,alias,file;{details_legend},tags,copyright,text;',
    '__suffix__' => '{additional_fields_legend};{variants_legend},addAdditionalFiles;{protect_legend},protected;{publish_legend},published,start,stop;',
];

$dca['palettes']['default'] = Str::mergePalettes($dca['palettes']['__prefix__'], $dca['palettes']['__suffix__']);

$dca['subpalettes'] = [
    'addAdditionalFiles' => 'additionalFiles',
    'protected' => 'groups',
];

$contao5 = !\defined('VERSION');

$dca['config'] = [
    'dataContainer' => DC_Table::class,
    'ptable' => $archiveTable,
    'enableVersioning' => true,
    'sql' => [
        'keys' => [
            'id' => 'primary',
            'pid' => 'index',
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
        'headerFields' => ['title', 'type', 'tstamp'],
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
        $contao5 ? 'edit' : 'editheader' => [
            'href' => 'act=edit',
            'icon' => $contao5 ? 'edit.svg' : 'header.svg',
        ],
        'delete' => [
            'href' => 'act=delete',
            'icon' => 'delete.svg',
            'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '')
                .'\'))return false;Backend.getScrollOffset()"',
        ],
        'toggle' => [
            'icon' => 'visible.svg',
            'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
            'button_callback' => [ProductContainer::class, 'toggleIcon'],
        ],
        'show' => [
            'href' => 'act=show',
            'icon' => 'show.svg',
        ],
    ],
];

$dca['fields'] = [
    'id' => [
        'sql' => 'int(10) unsigned NOT NULL auto_increment',
    ],
    'pid' => [
        'foreignKey' => "$archiveTable.title",
        'exclude' => true,
        'search' => true,
        'sql' => "int(10) unsigned NOT NULL default '0'",
        'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
    ],
    'tstamp' => [
        'sql' => "int(10) unsigned NOT NULL default '0'",
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
        // no SQL! this is loaded onload from the parent archive
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
    'text' => [
        'exclude' => true,
        'search' => true,
        'inputType' => 'textarea',
        'eval' => ['tl_class' => 'clr', 'rte' => 'tinyMCE'],
        'sql' => 'text NULL',
    ],
    'tags' => [
        'exclude' => true,
        'inputType' => 'cfgTags',
        'eval' => [
            'tagsManager' => 'huh_media_library_item',
            'tl_class' => 'clr',
            'isAdditionalField' => true,
        ],
    ],
    'published' => [
        'exclude' => true,
        'filter' => true,
        'inputType' => 'checkbox',
        'eval' => ['doNotCopy' => true, 'tl_class' => 'clr'],
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
    'protected' => [
        'label' => &$GLOBALS['TL_LANG'][$archiveTable]['protected'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'eval' => ['submitOnChange' => true],
        'sql' => "char(1) NOT NULL default ''",
    ],
    'groups' => [
        'label' => &$GLOBALS['TL_LANG'][$archiveTable]['groups'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'foreignKey' => 'tl_member_group.name',
        'eval' => ['mandatory' => true, 'multiple' => true],
        'sql' => 'blob NULL',
    ],
];

Category::addMultipleCategoriesFieldToDca(
    $table,
    'categories',
    [
        'addPrimaryCategory' => false,
        'mandatory' => false,
        'parentsUnselectable' => true,
        'isAdditionalField' => true,
    ]
);
