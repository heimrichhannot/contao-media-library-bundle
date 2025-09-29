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
$lang['protected'] = ['Downloadelemente schützen', 'Wählen Sie diese Option, wenn der Zugriff auf die Downloadelemente beschränkt werden soll.'];
$lang['groups'] = ['Mitgliedergruppen', 'Wählen Sie hier die gewünschten Mitgliedergruppen für den geschützten Zugriff aus.'];
$lang['keepProductTitleForDownloadItems'] = ['Produktnamen im Downloadtitel behalten', 'Wählen Sie diese Option, wenn der Titel des Produktes in den Titeln der Downloadelementen bestehen bleiben soll.'];
$lang['enableDelete'] = ['Produkte können gelöscht werden', 'Wählen Sie diese Option, wenn dem Nutzer die Möglichkeit gegeben werden soll, das Produkt zu löschen.'];
$lang['deleteJumpTo'] = ['Weiterleitungsseite nach dem Löschen', 'Wählen Sie hier die Seite aus, zu der der Nutzer nach dem Löschen des Produktes weitergeleitet werden soll.'];
$lang['groupsCanDeleteOwn'] = ['Eigene Produkte löschen (Veraltet, bitte in Mitglieder(gruppen)einstellungen setzen)', 'Wählen Sie hier die Mitgliedergruppen aus, die ihre eigenen Produkte löschen dürfen.'];
$lang['groupsCanDeleteAll'] = ['Alle Produkte löschen (Veraltet, bitte in Mitglieder(gruppen)einstellungen setzen)', 'Wählen Sie hier die Mitgliedergruppen aus, die alle Produkte löschen dürfen.'];
$lang['enableCreate'] = ['Produkte können erstellt werden', 'Wählen Sie diese Option, wenn den Nutzern die Möglichkeit gegeben werden soll, neue Produkte im Frontend zu erstellen.'];
$lang['enableEdit'] = ['Produkte können bearbeitet werden', 'Wählen Sie diese Option, wenn den Nutzern die Möglichkeit gegeben werden soll, das Produkt im Frontend zu bearbeiten.'];
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
$lang['new'] = ['Neue Produkt-Archiv', 'Produkt-Archiv erstellen'];
$lang['edit'] = ['Produkte aus Archiv anzeigen', 'Produkte aus Archiv ID %s anzeigen'];
$lang['editheader'] = ['Produkt-Archiv bearbeiten', 'Produkt-Archiv ID %s bearbeiten'];
$lang['copy'] = ['Produkt-Archiv duplizieren', 'Produkt-Archiv ID %s duplizieren'];
$lang['delete'] = ['Produkt-Archiv löschen', 'Produkt-Archiv ID %s löschen'];
$lang['toggle'] = ['Produkt-Archiv veröffentlichen', 'Produkt-Archiv ID %s veröffentlichen/verstecken'];
$lang['show'] = ['Produkt-Archiv Details', 'Produkt-Archiv Details ID %s anzeigen'];
