<?php

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;

$lang = &$GLOBALS['TL_LANG'][ArchiveModel::getTable()];

/**
 * Fields
 */
$lang['tstamp'] = ['Änderungsdatum', ''];
$lang['title'] = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['type'] = ['Typ', 'Wählen Sie hier aus, von welcher Art die Einträge dieses Archives sind.'];
$lang['jumpTo'] = ['Weiterleitungsseite', 'Wählen Sie hier die Produktleser-Seite aus, zu der Besucher nach dem Klicken auf ein Produkt weitergeleitet werden soll.'];
$lang['additionalFields'] = ['Zusätzliche Felder der Einträge', 'Wählen Sie hier die Felder aus, die der Palette von Produkten dieses Archivs hinzugefügt werden sollen.'];
$lang['imageSizes'] = ['Bildgrößen', 'Wählen Sie hier die Bildgrößen aus, für die Downloads für das Produkt erstellt werden sollen.'];

$lang['enableDelete'] = ['Einträge können gelöscht werden', 'Wählen Sie diese Option, wenn dem Nutzer die Möglichkeit gegeben werden soll, das Produkt zu löschen.'];
$lang['deleteJumpTo'] = ['Weiterleitungsseite nach dem Löschen', 'Wählen Sie hier die Seite aus, zu der der Nutzer nach dem Löschen des Produktes weitergeleitet werden soll.'];

$lang['enableCreate'] = ['Neue Einträge können erstellt werden', 'Wählen Sie diese Option, wenn den Nutzern die Möglichkeit gegeben werden soll, neue Produkte im Frontend zu erstellen.'];
$lang['createJumpTo'] = ['Erstellen-Seite', 'Wählen Sie hier die Seite aus, auf der sich das Formular zum Erstellen eines neuen Produktes befindet.'];

$lang['enableEdit'] = ['Einträge können bearbeitet werden', 'Wählen Sie diese Option, wenn den Nutzern die Möglichkeit gegeben werden soll, das Produkt im Frontend zu bearbeiten.'];
$lang['editJumpTo'] = ['Bearbeiten-Seite', 'Wählen Sie hier die Seite aus, auf der sich das Formular zum Bearbeiten des Produktes befindet.'];

/**
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['advanced_legend'] = 'Erweiterte Einstellungen';
$lang['image_legend'] = 'Bildeinstellungen';
$lang['edit_legend'] = 'Bearbeitungseinstellungen';
$lang['protect_legend'] = 'Zugriffsschutz';
$lang['publish_legend'] = 'Veröffentlichung';

/**
 * Buttons
 */
$lang['new'] = ['Neues Mediathek-Archiv', 'Mediathek-Archiv erstellen'];
$lang['edit'] = ['Einträge aus Archiv anzeigen', 'Einträge aus Archiv ID %s anzeigen'];
$lang['editheader'] = ['Mediathek-Archiv bearbeiten', 'Mediathek-Archiv ID %s bearbeiten'];
$lang['copy'] = ['Mediathek-Archiv duplizieren', 'Mediathek-Archiv ID %s duplizieren'];
$lang['delete'] = ['Mediathek-Archiv löschen', 'Mediathek-Archiv ID %s löschen'];
$lang['toggle'] = ['Mediathek-Archiv veröffentlichen', 'Mediathek-Archiv ID %s veröffentlichen/verstecken'];
$lang['show'] = ['Mediathek-Archiv Details', 'Details von Mediathek-Archiv ID %s anzeigen'];
