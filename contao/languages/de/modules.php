<?php

use HeimrichHannot\MediaLibraryBundle\Contao\BackendModule;
use HeimrichHannot\MediaLibraryBundle\Controller\FrontendModule\ProductListModuleController;

$GLOBALS['TL_LANG']['MOD'][BackendModule::NAME] = ['Mediathek', ''];

$GLOBALS['TL_LANG']['FMD']['media-library'] = 'Mediathek';
$GLOBALS['TL_LANG']['FMD'][ProductListModuleController::TYPE] = ['Produkt-Liste', 'Eine Liste von Mediathek-Produkten ausgeben.'];
