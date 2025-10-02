<?php

use HeimrichHannot\MediaLibraryBundle\Flare\ListType\MediaLibraryArchiveListType;

$lang = &$GLOBALS['TL_LANG'];
$flare = &$lang['FLARE'];

$lang['MSC']['contaoMediaLibraryBundle'] = [
    'messageOriginalFileKept' => 'Das Produkt wurde erfolgreich gelöscht. Achtung: die Originaldatei ist immer noch in der Dateiverwaltung vorhanden, um Datenverlust zu vermeiden.',
    'additional' => 'zusätzlich'
];

$flare['list'][MediaLibraryArchiveListType::TYPE] = ['Mediathek-Archiv [ML]', 'Listet Elemente eines Mediathek-Archives auf.'];
