<?php

$lang = &$GLOBALS['TL_LANG']['tl_ml_product_archive'];

/**
 * Fields
 */
$lang['tstamp']                          = ['Änderungsdatum', ''];
$lang['title']                           = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['type']                            = ['Typ', 'Wählen Sie hier den Typ aus, den Produkte dieses Archivs erhalten sollen.'];
$lang['additionalFields']                = ['Zusätzliche Produktfelder', 'Wählen Sie hier die Felder aus, die der Palette von Produkten dieses Archivs hinzugefügt werden sollen.'];
$lang['imageSizes']                      =
    ['Bildgrößen', 'Wählen Sie hier die Bildgrößen aus, für die Downloads für das Produkt erstellt werden sollen.'];
$lang['uploadFolderMode']                = [
    'Uploadverzeichnis-Modus',
    'Wählen Sie hier aus, wo Dateien abgelegt werden, die '
];
$lang['uploadFolder']                    = [
    'Uploadverzeichnis',
    'Wählen Sie hier den Ordner, der als Basisordner für die hochgeladenen Dateien genutzt werden soll. (In diesem Ordner werden Unterverzeichnisse anhand des Produktnamens angelegt. In diesem werden dann wiederum die Dateien abgelegt.)'
];
$lang['uploadFolderUserPattern']         = [
    'Verzeichnismuster (aktueller Benutzer)',
    'Geben Sie hier ein Muster für den Teil des Uploadverzeichnispfads ein, der durch den akuellen Backend-Benutzer generiert werden soll (Beispiel "%name%-%id%").'
];
$lang['addProductPatternToUploadFolder'] = [
    'Aktuelles Produkt zum Uploadverzeichnispfad hinzufügen',
    'Wählen Sie diese Option, wenn der Uploadverzeichnispfad auch durch das aktuelle Produkt bestimmt werden soll.'
];
$lang['uploadFolderProductPattern']      = [
    'Verzeichnismuster (aktuelles Produkt)',
    'Geben Sie hier ein Muster für den Teil des Uploadverzeichnispfads ein, der durch das akuelle Produkt generiert werden soll (Beispiel "%title%-%id%").'
];

/**
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['config_legend']  = 'Konfiguration';

/**
 * Buttons
 */
$lang['new']        = ['Neue Produkt-Archiv', 'Produkt-Archiv erstellen'];
$lang['edit']       = ['Produkte aus Archiv anzeigen', 'Produkte aus Archiv ID %s anzeigen'];
$lang['editheader'] = ['Produkt-Archiv bearbeiten', 'Produkt-Archiv ID %s bearbeiten'];
$lang['copy']       = ['Produkt-Archiv duplizieren', 'Produkt-Archiv ID %s duplizieren'];
$lang['delete']     = ['Produkt-Archiv löschen', 'Produkt-Archiv ID %s löschen'];
$lang['toggle']     = ['Produkt-Archiv veröffentlichen', 'Produkt-Archiv ID %s veröffentlichen/verstecken'];
$lang['show']       = ['Produkt-Archiv Details', 'Produkt-Archiv Details ID %s anzeigen'];