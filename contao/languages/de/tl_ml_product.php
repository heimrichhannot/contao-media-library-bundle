<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

$lang = &$GLOBALS['TL_LANG']['tl_ml_product'];

/*
 * Fields
 */
$lang['tstamp'] = ['Änderungsdatum', ''];
$lang['title'] = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['pid'] = ['Kategorie', 'Wählen Sie hier die Kategorie aus.'];
$lang['file'] = ['Datei', 'Laden Sie hier die zum Produkt gehörige Datei hoch.'];
$lang['videoPosterImage'] = ['Video-Vorschaubild', 'Laden Sie hier en Vorschaubild für das Video hoch.'];
$lang['alias'] = ['Alias', 'Der Alias ist eine eindeutige Referenz, die anstelle der numerischen ID aufgerufen werden kann.'];
$lang['copyright'] = ['Copyright', 'Geben Sie hier ein Copyright ein.'];
$lang['text'] = ['Beschreibung', 'Tragen Sie hier die Beschreibung für das Produkt ein.'];
$lang['tags'] = ['Schlagworte', 'Tragen Sie hier Schlagworte für das Produkt ein.'];
$lang['doNotCreateDownloadItems'] = [
    'Keine Download-Items erzeugen',
    'Wählen Sie diese Option, wenn für dieses Produkt keine Download-Items aus den hochgeladenen Dateien erstellt werden sollen.',
];
$lang['addAdditionalFiles'] = ['Zusätzliche Dateien hinzufügen', 'Wählen Sie diese Option, um dem Produkt weitere Dateien hinzuzufügen (bspw. für Bildergallerien).'];
$lang['additionalFiles'] = ['Zusätzliche Dateien', 'Wählen Sie hier die gewünschten Bilder aus.'];
$lang['published'] = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start'] = ['Anzeigen ab', 'Produkt erst ab diesem Tag auf der Webseite anzeigen.'];
$lang['stop'] = ['Anzeigen bis', 'Produkt nur bis zu diesem Tag auf der Webseite anzeigen.'];

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['product_legend'] = 'Produkt-Einstellungen';
$lang['additional_fields_legend'] = 'Zusätzliche Einstellungen';
$lang['publish_legend'] = 'Veröffentlichung';
$lang['protected_legend'] = 'Zugriffsschutz';

/*
 * Reference
 */
$lang['reference'] = [
    \HeimrichHannot\MediaLibraryBundle\DataContainer\ProductContainer::TYPE_IMAGE => 'Bild',
    \HeimrichHannot\MediaLibraryBundle\DataContainer\ProductContainer::TYPE_FILE => 'Datei',
    \HeimrichHannot\MediaLibraryBundle\DataContainer\ProductContainer::TYPE_VIDEO => 'Video',
];

/*
 * Buttons
 */
$lang['new'] = ['Neues Produkt', 'Produkt erstellen'];
$lang['edit'] = ['Produkt bearbeiten', 'Produkt ID %s bearbeiten'];
$lang['copy'] = ['Produkt duplizieren', 'Produkt ID %s duplizieren'];
$lang['delete'] = ['Produkt löschen', 'Produkt ID %s löschen'];
$lang['toggle'] = ['Produkt veröffentlichen', 'Produkt ID %s veröffentlichen/verstecken'];
$lang['show'] = ['Produkt Details', 'Produkt-Details ID %s anzeigen'];
$lang['downloads'] = ['Downloads anzeigen', 'Downloads von Produkt ID %s anzeigen'];

$lang['downloadLink'] = 'herunterladen';
$lang['downloadTitle'] = '% herunterladen';
$lang['downloadItem'] = 'Option herunterladen';

$lang['closeModal'] = 'Schließen (Bei Änderungen speichern nicht vergessen!)';
