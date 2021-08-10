<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_ml_download'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable' => 'tl_ml_product',
        'enableVersioning' => true,
        'onload_callback' => [
            [\HeimrichHannot\MediaLibraryBundle\DataContainer\DownloadContainer::class, 'checkPermission'],
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
                'pid,start,stop,published' => 'index',
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
            'headerFields' => ['title'],
            'panelLayout' => 'filter;sort,search,limit',
            'child_record_callback' => [\HeimrichHannot\MediaLibraryBundle\DataContainer\DownloadContainer::class, 'listChildren'],
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
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                .'\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [\HeimrichHannot\MediaLibraryBundle\DataContainer\DownloadContainer::class, 'toggleIcon'],
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],
    'palettes' => [
        '__selector__' => ['published'],
        'default' => '{general_legend},title,authorType,author,imageSize,isAdditional,file;{publish_legend},published;',
    ],
    'subpalettes' => [
        'published' => 'start,stop',
    ],
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_ml_product.id',
            'exclude' => true,
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'originalDownload' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['tstamp'],
            'exclude' => true,
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateAdded' => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag' => 6,
            'eval' => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['title'],
            'exclude' => true,
            'search' => true,
            'sorting' => true,
            'flag' => 1,
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'file' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['file'],
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
        'imageSize' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['imageSize'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w50 clr', 'readonly' => true, 'includeBlankOption' => true],
            'options_callback' => [\HeimrichHannot\MediaLibraryBundle\DataContainer\ProductArchiveContainer::class, 'getImageSizes'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'isAdditional' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['isAdditional'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'w50', 'disabled' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['published'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'start' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['start'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'stop' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['stop'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];

\Contao\System::getContainer()->get('huh.utils.dca')->addAuthorFieldAndCallback('tl_ml_download');
