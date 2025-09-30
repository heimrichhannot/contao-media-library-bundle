<?php

use HeimrichHannot\MediaLibraryBundle\Security\Voter;

$lang = &$GLOBALS['TL_LANG']['tl_member'];

$lang['media_library_legend'] = 'Mediathek';
$lang['ml_archives'] = ['Mediathek-Archive', 'Wählen Sie hier die Mediatheken aus, für die das Mitglied Rechte bekommen soll.'];

$lang['ml_archivep'] = [
    'Mediathek-Rechte',
    'Wählen Sie hier die Rechte aus, die das Mitglied für die ausgewählten Archive erhalten soll.',
    Voter::PERMISSION_CREATE => 'Einträge erstellen und Dateien hochladen',
    Voter::PERMISSION_EDIT => 'Einträge bearbeiten',
    Voter::PERMISSION_DELETE => 'Einträge löschen',
    Voter::PERMISSION_DELETE_OWN => 'Eigene Einträge löschen',
];
