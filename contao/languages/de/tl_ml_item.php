<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;

$lang = &$GLOBALS['TL_LANG'][ItemModel::getTable()];

/*
 * Fields
 */
$lang['tstamp'] = ['Änderungsdatum', ''];
$lang['title'] = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['pid'] = ['Kategorie', 'Wählen Sie hier die Kategorie aus.'];
$lang['file'] = ['Datei', 'Laden Sie hier die zum Mediathekobjekt gehörige Datei hoch.'];
$lang['videoPosterImage'] = ['Video-Vorschaubild', 'Laden Sie hier en Vorschaubild für das Video hoch.'];
$lang['alias'] = ['Alias', 'Der Alias ist eine eindeutige Referenz, die anstelle der numerischen ID aufgerufen werden kann.'];
$lang['copyright'] = ['Copyright', 'Geben Sie hier ein Copyright ein.'];
$lang['text'] = ['Beschreibung', 'Tragen Sie hier die Beschreibung für das Mediathekobjekt ein.'];
$lang['tags'] = ['Schlagworte', 'Tragen Sie hier Schlagworte für das Mediathekobjekt ein.'];
$lang['addAdditionalFiles'] = ['Datei-Varianten hinzufügen', 'Wählen Sie diese Option, um weitere Dateien hinzuzufügen, die das originale Motiv aufgreifen, als Varianten der Originaldatei sind.'];
$lang['additionalFiles'] = ['Zusätzliche Dateien', 'Wählen Sie hier zusätzliche Varianten zum Originaldateiinhalt aus.'];
$lang['published'] = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start'] = ['Anzeigen ab', 'Mediathekobjekt erst ab diesem Tag auf der Webseite anzeigen.'];
$lang['stop'] = ['Anzeigen bis', 'Mediathekobjekt nur bis zu diesem Tag auf der Webseite anzeigen.'];
$lang['author'] = ['Autor', 'Geben Sie hier den Autor des Mediathekobjekts ein.'];

/*
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['details_legend'] = 'Detaileinstellungen';
$lang['additional_fields_legend'] = 'Einstellungen zusätzlicher Felder';
$lang['variants_legend'] = 'Varianteneinstellungen';
$lang['publish_legend'] = 'Veröffentlichung';
$lang['protect_legend'] = 'Zugriffsschutz';
$lang['video_legend'] = 'Video-Einstellungen';

/*
 * Buttons
 */
$lang['new'] = ['Neues Mediathek-Objekt', 'Mediathekobjekt erstellen'];
$lang['edit'] = ['Mediathekobjekt bearbeiten', 'Mediathekobjekt ID %s bearbeiten'];
$lang['copy'] = ['Mediathekobjekt duplizieren', 'Mediathekobjekt ID %s duplizieren'];
$lang['delete'] = ['Mediathekobjekt löschen', 'Mediathekobjekt ID %s löschen'];
$lang['toggle'] = ['Mediathekobjekt veröffentlichen', 'Mediathekobjekt ID %s veröffentlichen/verstecken'];
$lang['show'] = ['Mediathekobjekt Details', 'Mediathekobjekt-Details ID %s anzeigen'];
$lang['downloads'] = ['Downloads anzeigen', 'Downloads von Mediathekobjekt ID %s anzeigen'];

$lang['downloadLink'] = 'herunterladen';
$lang['downloadTitle'] = '% herunterladen';
$lang['downloadItem'] = 'Option herunterladen';

$lang['closeModal'] = 'Schließen (Bei Änderungen speichern nicht vergessen!)';
