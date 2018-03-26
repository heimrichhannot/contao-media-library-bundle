<?php

$GLOBALS['TL_DCA']['tl_ml_download'] = [
    'config'      => [
        'dataContainer'     => 'Table',
        'ptable'            => 'tl_ml_product',
        'enableVersioning'  => true,
        'onload_callback'   => [
            ['tl_ml_download', 'checkPermission'],
        ],
        'onsubmit_callback' => [
            ['HeimrichHannot\Haste\Dca\General', 'setDateAdded'],
        ],
        'sql'               => [
            'keys' => [
                'id'                       => 'primary',
                'pid,start,stop,published' => 'index'
            ]
        ]
    ],
    'list'        => [
        'label'             => [
            'fields' => ['title'],
            'format' => '%s'
        ],
        'sorting'           => [
            'mode'        => 0,
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
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif'
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif'
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_ml_download']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                                . '\'))return false;Backend.getScrollOffset()"'
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_ml_download']['toggle'],
                'icon'            => 'visible.gif',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => ['tl_ml_download', 'toggleIcon']
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif'
            ],
        ]
    ],
    'palettes'    => [
        '__selector__' => ['published'],
        'default'      => '{general_legend},title,downloadFile;{publish_legend},published;'
    ],
    'subpalettes' => [
        'published' => 'start,stop'
    ],
    'fields'      => [
        'id'           => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid'          => [
            'foreignKey' => 'tl_ml_product.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager']
        ],
        'tstamp'       => [
            'label' => &$GLOBALS['TL_LANG']['tl_ml_download']['tstamp'],
            'sql'   => "int(10) unsigned NOT NULL default '0'"
        ],
        'dateAdded'    => [
            'label'   => &$GLOBALS['TL_LANG']['MSC']['dateAdded'],
            'sorting' => true,
            'flag'    => 6,
            'eval'    => ['rgxp' => 'datim', 'doNotCopy' => true],
            'sql'     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['title'],
            'exclude'   => true,
            'search'    => true,
            'sorting'   => true,
            'flag'      => 1,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''"
        ],
        'downloadFile' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_ml_download']['downloadFile'],
            'exclude'    => true,
            'inputType'  => 'multifileupload',
            'eval'       => [
                'tl_class'           => 'clr',
                'filesOnly'          => true,
                'fieldType'          => 'checkbox',
                'skipPrepareForSave' => true,
                'uploadFolder'       => ['tl_ml_download', 'getUploadFolder'],
                'addRemoveLinks'     => true,
                'maxFiles'           => 1,
                'multipleFiles'      => true,
                'maxUploadSize'      => \Config::get('maxFileSize'),
                'mandatory'          => true
            ],
            'attributes' => ['legend' => 'media_legend'],
            
            'sql' => "blob NULL",
        ],
        'published'    => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true, 'submitOnChange' => true],
            'sql'       => "char(1) NOT NULL default ''"
        ],
        'start'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['start'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql'       => "varchar(10) NOT NULL default ''"
        ],
        'stop'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_ml_download']['stop'],
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
$containerUtil->addAuthorFieldAndCallback('tl_ml_download');


class tl_ml_download extends \Contao\Backend
{
    public function getUploadFolder()
    {
        return 'files/media/mediathek/test';
    }
    
    
    public function listChildren($arrRow)
    {
        return '<div class="tl_content_left">' . ($arrRow['title'] ?: $arrRow['id']) . ' <span style="color:#b3b3b3; padding-left:3px">[' .
               \Date::parse(\Contao\Config::get('datimFormat'), trim($arrRow['dateAdded'])) . ']</span></div>';
    }
    
    public function checkPermission()
    {
        $user     = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();
        
        if ($user->isAdmin) {
            return;
        }
        
        // Set the root IDs
        if (!is_array($user->contao - media - library - bundles) || empty($user->contao - media - library - bundles)) {
            $root = [0];
        } else {
            $root = $user->contao - media - library - bundles;
        }
        
        $id = strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;
        
        // Check current action
        switch (\Contao\Input::get('act')) {
            case 'paste':
                // Allow
                break;
            
            case 'create':
                if (!strlen(\Contao\Input::get('pid')) || !in_array(\Contao\Input::get('pid'), $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create tl_ml_download items in tl_ml_download archive ID '
                                                                                 . \Contao\Input::get('pid') . '.');
                }
                break;
            
            case 'cut':
            case 'copy':
                if (!in_array(\Contao\Input::get('pid'), $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Contao\Input::get('act')
                                                                                 . ' tl_ml_download item ID ' . $id . ' to tl_ml_download archive ID '
                                                                                 . \Contao\Input::get('pid') . '.');
                }
            // NO BREAK STATEMENT HERE
            
            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $database->prepare("SELECT pid FROM tl_ml_download WHERE id=?")
                    ->limit(1)
                    ->execute($id);
                
                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid tl_ml_download item ID ' . $id . '.');
                }
                
                if (!in_array($objArchive->pid, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to ' . \Contao\Input::get('act')
                                                                                 . ' tl_ml_download item ID ' . $id . ' of tl_ml_download archive ID '
                                                                                 . $objArchive->pid . '.');
                }
                break;
            
            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access tl_ml_download archive ID ' . $id
                                                                                 . '.');
                }
                
                $objArchive = $database->prepare("SELECT id FROM tl_ml_download WHERE pid=?")
                    ->execute($id);
                
                if ($objArchive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid tl_ml_download archive ID ' . $id . '.');
                }
                
                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');
                
                $session                   = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $session->replace($session);
                break;
            
            default:
                if (strlen(\Contao\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "' . \Contao\Input::get('act') . '".');
                } elseif (!in_array($id, $root)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access tl_ml_download archive ID ' . $id
                                                                                 . '.');
                }
                break;
        }
    }
    
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();
        
        if (strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), (\Contao\Input::get('state') === '1'), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }
        
        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_ml_download::published', 'alexf')) {
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
        if (is_array($GLOBALS['TL_DCA']['tl_ml_download']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_download']['config']['onload_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}($dc);
                } elseif (is_callable($callback)) {
                    $callback($dc);
                }
            }
        }
        
        // Check the field access
        if (!$user->hasAccess('tl_ml_download::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish tl_ml_download item ID '
                                                                         . $intId . '.');
        }
        
        // Set the current record
        if ($dc) {
            $objRow = $database->prepare("SELECT * FROM tl_ml_download WHERE id=?")
                ->limit(1)
                ->execute($intId);
            
            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }
        
        $objVersions = new \Versions('tl_ml_download', $intId);
        $objVersions->initialize();
        
        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_download']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_download']['fields']['published']['save_callback'] as $callback) {
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
        $database->prepare("UPDATE tl_ml_download SET tstamp=$time, published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);
        
        if ($dc) {
            $dc->activeRecord->tstamp    = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }
        
        // Trigger the onsubmit_callback
        if (is_array($GLOBALS['TL_DCA']['tl_ml_download']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_download']['config']['onsubmit_callback'] as $callback) {
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
