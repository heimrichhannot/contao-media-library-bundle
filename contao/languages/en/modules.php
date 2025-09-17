<?php

use HeimrichHannot\MediaLibraryBundle\Contao\BackendModule;
use HeimrichHannot\MediaLibraryBundle\Controller\FrontendModule\ProductListModuleController;

$GLOBALS['TL_LANG']['MOD'][BackendModule::NAME] = ['Media library', ''];

$GLOBALS['TL_LANG']['FMD']['media_library'] = 'Media library';
$GLOBALS['TL_LANG']['FMD'][ProductListModuleController::TYPE] = ['Product list', 'Output a list of media library products.'];
