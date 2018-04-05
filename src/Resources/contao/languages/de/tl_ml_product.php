<?php

$lang = &$GLOBALS['TL_LANG']['tl_ml_product'];

/**
 * Fields
 */
$lang['tstamp']                   = ['Änderungsdatum', ''];
$lang['title']                    = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['pid']                      = ['Kategorie', 'Wählen Sie hier die Kategorie aus.'];
$lang['published']                = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start']                    = ['Anzeigen ab', 'Produkt erst ab diesem Tag auf der Webseite anzeigen.'];
$lang['stop']                     = ['Anzeigen bis', 'Produkt nur bis zu diesem Tag auf der Webseite anzeigen.'];
$lang['uploadedFiles']            = ['Dateien', 'Laden Sie hier die zum Produkt gehörigen Dateien hoch.'];
$lang['text']                     = ['Beschreibung', 'Tragen Sie hier die Beschreibung für das Produkt ein.'];
$lang['tag']                      = ['Schlagworte', 'Tragen Sie hier Schlagworte für das Produkt ein.'];
$lang['doNotCreateDownloadItems'] = [
    'Keine Download-Items erzeugen',
    'Wählen Sie diese Option, wenn für dieses Produkt keine Download-Items aus den hochgeladenen Dateien erstellt werden sollen.'
];
$lang['licence']                  = [
    'Lizenz',
    'Geben Sie hier die Lizenz für dieses Produkt an.',
    \HeimrichHannot\MediaLibraryBundle\Model\ProductModel::ITEM_LICENCE_TYPE_FREE   => 'frei',
    \HeimrichHannot\MediaLibraryBundle\Model\ProductModel::ITEM_LICENCE_TYPE_LOCKED => 'gesperrt'
];


/**
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['publish_legend'] = 'Veröffentlichung';
$lang['product_legend'] = 'Produkt-Einstellungen';

/**
 * Buttons
 */
$lang['new']       = ['Neues Produkt', 'Produkt erstellen'];
$lang['edit']      = ['Produkt bearbeiten', 'Produkt ID %s bearbeiten'];
$lang['copy']      = ['Produkt duplizieren', 'Produkt ID %s duplizieren'];
$lang['delete']    = ['Produkt löschen', 'Produkt ID %s löschen'];
$lang['toggle']    = ['Produkt veröffentlichen', 'Produkt ID %s veröffentlichen/verstecken'];
$lang['show']      = ['Produkt Details', 'Produkt-Details ID %s anzeigen'];
$lang['downloads'] = ['Downloads anzeigen', 'Downloads von Produkt ID %s anzeigen'];

$lang['downloadLink']  = 'herunterladen';
$lang['downloadTitle'] = '% herunterladen';
$lang['downloadItem']  = 'Option herunterladen';