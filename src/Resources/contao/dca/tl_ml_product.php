<?php

$GLOBALS['TL_DCA']['tl_ml_product'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_ml_product_archive',
        'ctable'            => 'tl_ml_download',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['huh.media_library.backend.product', 'modifyPalette']
        ],
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
            ['huh.media_library.backend.product', 'generateDownloadItems'],
            ['huh.media_library.backend.product', 'generateTags']
        ],
        'oncopy_callback'   => [
            ['huh.utils.dca', 'setDateAddedOnCopy'],
        ],
        'ondelete_callback' => [
            ['huh.media_library.backend.product', 'cleanGeneratedDownloadItems']
        ],
        'sql'               => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['category'],
            'panelLayout' => 'filter;sort,search,limit',
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
            'edit'          => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.svg'
            ],
            'editDownloads' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['editDownloads'],
                'href'  => 'table=tl_ml_download',
                'icon'  => 'down.svg'
            ],
            'copy'          => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg'
            ],
            'delete'        => [
                'label'      => &$GLOBALS['TL_LANG']['tl_ml_product']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle'        => [
                'label'           => &$GLOBALS['TL_LANG']['tl_ml_product']['toggle'],
                'icon'            => 'visible.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_ml_product', 'toggleIcon']
            ],
            'show'          => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['published'],
        'default'      => '{general_legend},title;{product_legend},uploadedFiles,doNotCreateDownloadItems,text,licence,tag,overrideImageSize;{publish_legend},published;'
    ],
    'subpalettes' => [
        'published' => 'start,stop'
    ],
    'fields'      => [
        'id'                       => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'                      => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['pid'],
            'foreignKey' => 'tl_ml_product_archive.id',
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager']
        ],
        'tstamp'                   => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'                => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'uploadedFiles'            => [
            'label'      => &$GLOBALS['TL_LANG']['tl_ml_product']['uploadedFiles'],
            'exclude'    => true,
            'inputType'  => 'multifileupload',
            'eval'       => [
                'tl_class'           => 'clr',
                'extensions'         => \Config::get('validImageTypes'),
                'filesOnly'          => true,
                'fieldType'          => 'checkbox',
                'maxImageWidth'      => \Config::get('gdMaxImgWidth'),
                'maxImageHeight'     => \Config::get('gdMaxImgHeight'),
                'skipPrepareForSave' => true,
                'uploadFolder'       => ['huh.media_library.backend.product', 'getUploadFolder'],
                'addRemoveLinks'     => true,
                'maxFiles'           => 15,
                'multipleFiles'      => true,
                'maxUploadSize'      => \Config::get('maxFileSize'),
                'mandatory'          => true
            ],
            'attributes' => ['legend' => 'media_legend'],
            
            'sql' => "blob NULL",
        ],
        'doNotCreateDownloadItems' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['doNotCreateDownloadItems'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'text'                     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['text'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'textarea',
            'eval'      => ['tl_class' => 'clr', 'rte' => 'tinyMCE'],
            'sql'       => "text NULL",
        ],
        'tag'                      => [
            'label'      => &$GLOBALS['TL_LANG']['tl_ml_product']['tag'],
            'exclude'    => true,
            'search'     => true,
            'sorting'    => true,
            'inputType'  => 'tagsinput',
            'eval'       => [
                'tl_class'       => 'long clr autoheight',
                'multiple'       => true,
                'freeInput'      => true,
                'trimValue'      => true,
                'decodeEntities' => true,
                'highlight'      => true
            ],
            'attributes' => ['legend' => 'general_legend', 'multilingual' => true, 'fixed' => true, 'fe_sorting' => true, 'fe_search' => true],
            'sql'        => "blob NULL",
        ],
        'licence' => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product']['licence'],
            'inputType' => 'select',
            'exclude'    => true,
            'default' => 'free',
            'reference' => &$GLOBALS['TL_LANG']['tl_ml_product']['licence'],
            'options' => [
                \HeimrichHannot\MediaLibraryBundle\Model\ProductModel::ITEM_LICENCE_TYPE_FREE,
                \HeimrichHannot\MediaLibraryBundle\Model\ProductModel::ITEM_LICENCE_TYPE_LOCKED
            ],
            'eval' => [
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'sql' => "varchar(16) NOT NULL default ''"
        ],
        'published'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'default'   => true,
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true, 'tl_class' => 'clr'],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'start'                    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'stop'                     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product']['stop'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'imageSize' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['imageSize'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ]
    ]
];

$containerUtil = \Contao\System::getContainer()->get('huh.utils.dca');
//$containerUtil->addOverridableFields(['imageSize'], 'tl_ml_product_archive', 'tl_ml_product');


class tl_ml_product extends \Contao\Backend
{
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();
        
        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') === '1'), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }
        
        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_ml_product::published', 'alexf')) {
            return '';
        }
        
        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);
        
        if (!$row['published']) {
            $icon = 'invisible.svg';
        }
        
        return '<a href="' . $this->addToUrl($href) . '" title="' . \StringUtil::specialchars($title) . '"' . $attributes . '>'
               . \Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }
    
    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user     = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();
        
        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');
        
        if ($dc) {
            $dc->id = $intId; // see #8043
        }
        
        // Trigger the onload_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }
        
        // Check the field access
        if (!$user->hasAccess('tl_ml_product::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish tl_ml_product item ID ' . $intId
                                                                         . '.');
        }
        
        // Set the current record
        if ($dc) {
            $objRow = $database->prepare("SELECT * FROM tl_ml_product WHERE id=?")
                ->limit(1)
                ->execute($intId);
            
            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }
        
        $objVersions = new \Versions('tl_ml_product', $intId);
        $objVersions->initialize();
        
        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $dc);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }
        
        $time = time();
        
        // Update the database
        $database->prepare("UPDATE tl_ml_product SET tstamp=$time, published=" . ($blnVisible ? '1' : '') . " WHERE id=?")
            ->execute($intId);
        
        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }
        
        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product']['config']['onsubmit_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }
        
        $objVersions->create();
    }
    
}
