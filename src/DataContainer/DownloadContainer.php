<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class DownloadContainer
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var FileUtil
     */
    private $fileUtil;

    public function __construct(ModelUtil $modelUtil, FileUtil $fileUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->fileUtil = $fileUtil;
    }

    public function listChildren($row)
    {
        $data = [$this->fileUtil->getPathFromUuid($row['file'])];

        if ($row['isAdditional']) {
            $data[] = $GLOBALS['TL_LANG']['MSC']['contaoMediaLibraryBundle']['additional'];
        }

        return '<div class="tl_content_left">'.($row['title'] ?: $row['id']).' <span style="color:#b3b3b3; padding-left:3px">['.
            implode(', ', $data).']</span></div>';
    }

    public function checkPermission()
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        if ($user->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!\is_array($user->contao_media_library_bundles) || empty($user->contao_media_library_bundles)) {
            $root = [0];
        } else {
            $root = $user->contao_media_library_bundles;
        }

        $id = \strlen(\Contao\Input::get('id')) ? \Contao\Input::get('id') : CURRENT_ID;
        $pid = 0;

        if (null !== ($product = $this->modelUtil->findModelInstanceByPk('tl_ml_product', $id))) {
            if (null !== ($archive = $this->modelUtil->findModelInstanceByPk('tl_ml_product_archive', $product->pid))) {
                $pid = $archive->id;
            }
        }

        // Check current action
        switch (\Contao\Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!\strlen($pid) || !\in_array($pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to create ml_download items in ml_download archive ID '.$pid.'.');
                }

                break;

            case 'cut':
            case 'copy':
                if (!\in_array($pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' ml_download item ID '.$id.' to ml_download archive ID '.$pid.'.');
                }
            // no break STATEMENT HERE

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $pid = 0;

                if (null !== ($download = $this->modelUtil->findModelInstanceByPk('tl_ml_download', $id))) {
                    if (null !== ($product = $this->modelUtil->findModelInstanceByPk('tl_ml_product', $download->pid))) {
                        if (null !== ($archive = $this->modelUtil->findModelInstanceByPk('tl_ml_product_archive', $product->pid))) {
                            $pid = $archive->id;
                        }
                    }
                }

                if (!$pid) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid ml_download item ID '.$id.'.');
                }

                if (!\in_array($pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to '.\Contao\Input::get('act').' ml_download item ID '.$id.' of ml_download archive ID '.$archive->pid.'.');
                }

                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!\in_array($id, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access ml_download archive ID '.$id.'.');
                }

                $archive = $database->prepare('SELECT id FROM tl_ml_download WHERE pid=?')
                    ->execute($id);

                if ($archive->numRows < 1) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid ml_download archive ID '.$id.'.');
                }

                /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface $session */
                $session = \System::getContainer()->get('session');

                $session = $session->all();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $archive->fetchEach('id'));
                $session->replace($session);

                break;

            default:
                if (\strlen(\Contao\Input::get('act'))) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Invalid command "'.\Contao\Input::get('act').'".');
                } elseif (!\in_array($pid, $root, true)) {
                    throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to access ml_download archive ID '.$id.'.');
                }

                break;
        }
    }

    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $user = \Contao\BackendUser::getInstance();

        if (\strlen(\Contao\Input::get('tid'))) {
            $this->toggleVisibility(\Contao\Input::get('tid'), ('1' === \Contao\Input::get('state')), (@func_get_arg(12) ?: null));
            Controller::redirect(System::getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$user->hasAccess('tl_ml_download::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.svg';
        }

        return '<a href="'.Controller::addToUrl($href).'&rt='.\RequestToken::get().'" title="'.\StringUtil::specialchars($title).'"'.$attributes.'>'.\Image::getHtml($icon, $label, 'data-state="'.($row['published'] ? 1 : 0).'"').'</a> ';
    }

    public function toggleVisibility($intId, $blnVisible, \DataContainer $dc = null)
    {
        $user = \Contao\BackendUser::getInstance();
        $database = \Contao\Database::getInstance();

        // Set the ID and action
        \Contao\Input::setGet('id', $intId);
        \Contao\Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        // Trigger the onload_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_ml_download']['config']['onload_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_download']['config']['onload_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        // Check the field access
        if (!$user->hasAccess('tl_ml_download::published', 'alexf')) {
            throw new \Contao\CoreBundle\Exception\AccessDeniedException('Not enough permissions to publish/unpublish ml_download item ID '.$intId.'.');
        }

        // Set the current record
        if ($dc) {
            $objRow = $database->prepare('SELECT * FROM tl_ml_download WHERE id=?')
                ->limit(1)
                ->execute($intId);

            if ($objRow->numRows) {
                $dc->activeRecord = $objRow;
            }
        }

        $objVersions = new \Versions('tl_ml_download', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_ml_download']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_download']['fields']['published']['save_callback'] as $callback) {
                if (\is_array($callback)) {
                    $blnVisible = System::importStatic($callback[0])->{$callback[1]}($blnVisible, $dc);
                } elseif (\is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $dc);
                }
            }
        }

        $time = time();

        // Update the database
        $database->prepare("UPDATE tl_ml_download SET tstamp=$time, published='".($blnVisible ? '1' : "''")."' WHERE id=?")
            ->execute($intId);

        if ($dc) {
            $dc->activeRecord->tstamp = $time;
            $dc->activeRecord->published = ($blnVisible ? '1' : '');
        }

        // Trigger the onsubmit_callback
        if (\is_array($GLOBALS['TL_DCA']['tl_ml_download']['config']['onsubmit_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_ml_download']['config']['onsubmit_callback'] as $callback) {
                if (\is_array($callback)) {
                    System::importStatic($callback[0])->{$callback[1]}($dc);
                } elseif (\is_callable($callback)) {
                    $callback($dc);
                }
            }
        }

        $objVersions->create();
    }
}
