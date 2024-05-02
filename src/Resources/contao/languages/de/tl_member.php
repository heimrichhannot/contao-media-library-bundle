<?php

use HeimrichHannot\MediaLibraryBundle\Security\ProductVoter;

$lang = &$GLOBALS['TL_LANG']['tl_member'];

$lang['media_library_legend'] = 'Mediathek';
$lang['ml_archives'] = ['Mediathek-Archive', 'Wählen Sie hier die Mediatheken aus, für die das Mitglied Rechte bekommen soll.'];
$lang['ml_archivesp'] = ['Archiv-Rechte', 'Wählen Sie hier die Rechte aus, die das Mitglied für die ausgewählten Archive erhalten soll.'];

$lang['ml_archivesp'][ProductVoter::PERMISSION_EDIT] = 'Produkte bearbeiten';
$lang['ml_archivesp'][ProductVoter::PERMISSION_DELETE] = 'Produkte löschen';
$lang['ml_archivesp'][ProductVoter::PERMISSION_DELETE_OWN] = 'Eigene Produkte löschen';
