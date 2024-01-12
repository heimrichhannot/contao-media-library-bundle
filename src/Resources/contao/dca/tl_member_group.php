<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use HeimrichHannot\MediaLibraryBundle\Security\ProductVoter;

$dca = &$GLOBALS['TL_DCA']['tl_member_group'];

ProductVoter::createAccessRightFields($dca);

PaletteManipulator::create()
    ->addLegend('media_library_legend', 'title_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField('ml_archives', 'media_library_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('ml_archivesp', 'media_library_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_member_group');