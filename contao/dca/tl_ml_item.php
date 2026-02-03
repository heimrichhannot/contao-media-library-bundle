<?php /** @noinspection PhpUndefinedNamespaceInspection, PhpUndefinedClassInspection */

use Codefog\TagsBundle\CodefogTagsBundle;
use Contao\DC_Table;
use Contao\Config;
use HeimrichHannot\CategoriesBundle\Backend\Category;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\MediaLibraryBundle\Util\Str;
use HeimrichHannot\UtilsBundle\Dca\AliasField;
use HeimrichHannot\UtilsBundle\Dca\AuthorField;
use HeimrichHannot\UtilsBundle\Dca\DateAddedField;

$table = ItemModel::getTable();
$archiveTable = ArchiveModel::getTable();

AuthorField::register($table)
    ->setType(AuthorField::TYPE_MEMBER)
    ->setEvalValue('mandatory', false)
    ->setEvalValue('isAdditionalField', true)
;
DateAddedField::register($table);
AliasField::register($table);

$dca = &$GLOBALS['TL_DCA'][$table];

$dca['palettes'] = [
    '__selector__' => ['type', 'addAdditionalFiles'],
    '__prefix__' => '{general_legend},title,alias,file,_filecredits_copyright;{details_legend},tags,text;',
    '__suffix__' => '{additional_fields_legend};{variants_legend},addAdditionalFiles;{publish_legend},published,start,stop;',
];

$dca['palettes']['default'] = Str::mergePalettes($dca['palettes']['__prefix__'], $dca['palettes']['__suffix__']);

$dca['subpalettes'] = [
    'addAdditionalFiles' => 'additionalFiles',
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
        'headerFields' => ['title', 'tstamp'],
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
        ($contao5 ? 'edit' : 'editheader') => [
            'href' => 'act=edit',
            'icon' => $contao5 ? 'edit.svg' : 'header.svg',
        ],
        'delete' => [
            'href' => 'act=delete',
            'icon' => 'delete.svg',
            'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '').'\'))return false;Backend.getScrollOffset()"',
        ],
        'toggle' => [
            'icon' => 'visible.svg',
            'attributes' => 'onclick="Backend.getScrollOffset();"',
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
    // 'type' => [
    //     'exclude' => true,
    //     'filter' => false,
    //     'search' => false,
    //     'sorting' => false,
    //     // no SQL! this is loaded onload from the parent archive
    // ],
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
            'doNotCopy' => false,
            'unique' => false,
            'submitOnChange' => true,
        ],
        'sql' => [
            'type' => 'binary',
            'length' => 16,
            'fixed' => true,
            'notnull' => false,
        ],
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
            'tl_class' => 'autoheight clr',
            'multiple' => true,
            'fieldType' => 'checkbox',
            'filesOnly' => true,
            'mandatory' => true,
            'doNotCopy' => false,
            'sortable' => true,
        ],
        'sql' => 'blob NULL',
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
];

if (\class_exists(CodefogTagsBundle::class))
{
    $dca['fields']['tags'] = [
        'exclude' => true,
        'inputType' => 'cfgTags',
        'eval' => [
            'tagsManager' => 'huh_media_library_item',
            'tl_class' => 'clr',
            'isAdditionalField' => false,
        ],
    ];
}

if (\class_exists(Category::class))
{
    Category::addMultipleCategoriesFieldToDca(
        table: $table,
        name: 'categories',
        evalOverride: [
            'addPrimaryCategory' => false,
            'mandatory' => false,
            'parentsUnselectable' => true,
            'isAdditionalField' => true,
        ],
    );
}
