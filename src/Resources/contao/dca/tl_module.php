<?php

use HeimrichHannot\MediaLibraryBundle\Controller\FrontendModule\ProductListModuleController;

$dca = &$GLOBALS['TL_DCA']['tl_module'];

$dca['palettes'][ProductListModuleController::TYPE] = '{title_legend},name,headline,type;{config_legend},ml_archives,numberOfItems,perPage;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

$dca['fields']['ml_archives'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'foreignKey'              => 'tl_ml_product_archive.title',
    'eval'                    => ['multiple'=>true],
    'sql'                     => "blob NULL"
];
