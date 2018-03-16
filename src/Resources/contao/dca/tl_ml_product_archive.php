<?php

\Contao\Controller::loadDataContainer('tl_ml_product');

$GLOBALS['TL_DCA']['tl_ml_product_archive'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ctable'            => 'tl_ml_product',
        'enableVersioning'  => true,
        'onsubmit_callback' => [
            ['huh.utils.dca', 'setDateAdded'],
        ],
        'sql'               => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'         => 0,
            'fields'       => ['title'],
            'headerFields' => ['title'],
            'panelLayout'  => 'filter;sort,search,limit'
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
            'showProducts' => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['showProducts'],
                'href'  => 'table=tl_ml_product',
                'icon'  => 'edit.svg',
            ],
            'edit'         => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'header.svg'
            ],
            'copy'         => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.svg'
            ],
            'delete'       => [
                'label'      => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle'       => [
                'label'           => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['toggle'],
                'icon'            => 'visible.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_ml_product', 'toggleIcon']
            ],
            'show'         => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.svg'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['createTagsFromValues'],
        'default'      => '{general_legend},title,palette,imageSize,createTagsFromValues,uploadFolder;{publish_legend},published;'
    ],
    'subpalettes' => [
        'createTagsFromValues' => 'fieldsForTags'
    ],
    'fields'      => [
        'id'                   => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'               => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'            => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'                => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'palette'              => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['palette'],
            'exclude'          => true,
            'flag'             => 1,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.media_library.backend.product_archive', 'getPaletteFields'],
            'eval'             => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql'              => "blob NULL"
        ],
        'imageSize'            => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['imageSize'],
            'exclude'          => true,
            'flag'             => 1,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.media_library.backend.product_archive', 'getImageSizes'],
            'eval'             => ['includeBlankOption' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql'              => "blob NULL"
        ],
        'uploadFolder'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['uploadFolder'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql'       => "binary(16) NULL",
        ],
        'createTagsFromValues' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['createTagsFromValues'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'fieldsForTags'        => [
            'label'            => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['fieldsForTags'],
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'options_callback' => ['huh.media_library.backend.product_archive', 'getFieldsForTag'],
            'eval'             => ['mandatory' => true, 'multiple' => true, 'tl_class' => 'clr'],
            'sql'              => "blob NULL"
        ],
        
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_product_archive']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
    ]
];


class tl_ml_product_archive extends \Contao\Backend
{
    
    
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();
        
        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') === '1'), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }
        
        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_ml_product_archive::published', 'alexf')) {
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
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product_archive']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product_archive']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }
        
        // Check the field access
        if (!$user->hasAccess('tl_ml_product_archive::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish tl_ml_product_archive item ID '
                                                                         . $intId . '.');
        }
        
        // Set the current record
        if ($dc) {
            $objRow = $database->prepare("SELECT * FROM tl_ml_product_archive WHERE id=?")
                ->limit(1)
                ->execute($intId);
            
            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }
        
        $objVersions = new \Versions('tl_ml_product_archive', $intId);
        $objVersions->initialize();
        
        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product_archive']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product_archive']['fields']['published']['save_callback'] as $callback) {
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
        $database->prepare("UPDATE tl_ml_product_archive SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);
        
        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }
        
        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_product_archive']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_product_archive']['config']['onsubmit_callback'] as $callback) {
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
