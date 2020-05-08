<?php

$dca = &$GLOBALS['TL_DCA']['tl_list_config'];

/**
 * Config
 */
$dca['config']['onload_callback']['mediaLibraryBundle'] = [\HeimrichHannot\MediaLibraryBundle\DataContainer\ListConfigContainer::class, 'modifyDca'];
