<?php

$lang = &$GLOBALS['TL_LANG']['tl_ml_product_archive'];

/**
 * Fields
 */
$lang['tstamp'][0]                            = 'Änderungsdatum';
$lang['tstamp'][1]                            = '';
$lang['title'][0]                             = 'Titel';
$lang['title'][0]                             = 'Geben Sie hier bitte den Titel ein.';
$lang['type'][0]                              = 'Typ';
$lang['type'][1]                              = 'Wählen Sie hier den Typ aus, den Produkte dieses Archivs erhalten sollen.';
$lang['additionalFields'][0]                  = 'Zusätzliche Produktfelder';
$lang['additionalFields'][1]                  = 'Wählen Sie hier die Felder aus, die der Palette von Produkten dieses Archivs hinzugefügt werden sollen.';
$lang['imageSizes'][0]                        = 'Bildgrößen';
$lang['imageSizes'][1]                        = 'Wählen Sie hier die Bildgrößen aus, für die Downloads für das Produkt erstellt werden sollen.';
$lang['uploadFolderMode'][0]                  = 'Uploadverzeichnis-Modus';
$lang['uploadFolderMode'][1]                  = 'Wählen Sie hier aus, wo Dateien abgelegt werden, die ';
$lang['uploadFolder'][0]                      = 'Uploadverzeichnis';
$lang['uploadFolder'][1]                      = 'Wählen Sie hier den Ordner, der als Basisordner für die hochgeladenen Dateien genutzt werden soll. (In diesem Ordner werden Unterverzeichnisse anhand des Produktnamens angelegt. In diesem werden dann wiederum die Dateien abgelegt.)';
$lang['uploadFolderUserPattern'][0]           = 'Verzeichnismuster (aktueller Benutzer)';
$lang['uploadFolderUserPattern'][1]           = 'Geben Sie hier ein Muster für den Teil des Uploadverzeichnispfads ein, der durch den akuellen Backend-Benutzer generiert werden soll (Beispiel "%name%-%id%").';
$lang['addProductPatternToUploadFolder'][0]   = 'Aktuelles Produkt zum Uploadverzeichnispfad hinzufügen';
$lang['addProductPatternToUploadFolder'][1]   = 'Wählen Sie diese Option, wenn der Uploadverzeichnispfad auch durch das aktuelle Produkt bestimmt werden soll.';
$lang['uploadFolderProductPattern'][0]        = 'Verzeichnismuster (aktuelles Produkt)';
$lang['uploadFolderProductPattern'][1]        = 'Geben Sie hier ein Muster für den Teil des Uploadverzeichnispfads ein, der durch das akuelle Produkt generiert werden soll (Beispiel "%title%-%id%").';
$lang['protected'][0]                         = 'Downloadelemente schützen';
$lang['protected'][1]                         = 'Wählen Sie diese Option, wenn der Zugriff auf die Downloadelemente beschränkt werden soll.';
$lang['keepProductTitleForDownloadItems'][0]  = 'Produktnamen im Downloadtitel behalten';
$lang['keepProductTitleForDownloadItems'][1]  = 'Wählen Sie diese Option, wenn der Titel des Produktes in den Titeln der Downloadelementen bestehen bleiben soll.';
$lang['preventLockedProductsFromDownload'][0] = 'Download von lizenzpflichtigen Produkten verbieten';
$lang['preventLockedProductsFromDownload'][1] = 'Wählen Sie diese Option, wenn lizenzpflichtige Produkte vom Download ausgeschlossen werden sollen.';
$lang['lockedProductText'][0]                 = 'Text bei lizenzpflichtigen Produkten';
$lang['lockedProductText'][1]                 = 'Wählen Sie hier den Text der angezeigt werden soll, wenn es sich um ein lizenzpflichtiges Produkt handelt';


/**
 * Reference
 */
$lang['reference'][\HeimrichHannot\MediaLibraryBundle\Backend\ProductArchive::UPLOAD_FOLDER_MODE_STATIC]        = 'Statisch';
$lang['reference'][\HeimrichHannot\MediaLibraryBundle\Backend\ProductArchive::UPLOAD_FOLDER_MODE_USER_HOME_DIR] = 'Benutzerverzeichnis';

/**
 * Legends
 */
$lang['general_legend']   = 'Allgemeine Einstellungen';
$lang['config_legend']    = 'Konfiguration';
$lang['protected_legend'] = 'Zugriffsschutz';

/**
 * Buttons
 */
$lang['new'][0]        = 'Neue Produkt-Archiv';
$lang['new'][1]        = 'Produkt-Archiv erstellen';
$lang['edit'][0]       = 'Produkte aus Archiv anzeigen';
$lang['edit'][1]       = 'Produkte aus Archiv ID %s anzeigen';
$lang['editheader'][0] = 'Produkt-Archiv bearbeiten';
$lang['editheader'][1] = 'Produkt-Archiv ID %s bearbeiten';
$lang['copy'][0]       = 'Produkt-Archiv duplizieren';
$lang['copy'][1]       = 'Produkt-Archiv ID %s duplizieren';
$lang['delete'][0]     = 'Produkt-Archiv löschen';
$lang['delete'][1]     = 'Produkt-Archiv ID %s löschen';
$lang['toggle'][0]     = 'Produkt-Archiv veröffentlichen';
$lang['toggle'][1]     = 'Produkt-Archiv ID %s veröffentlichen/verstecken';
$lang['show'][0]       = 'Produkt-Archiv Details';
$lang['show'][1]       = 'Produkt-Archiv Details ID %s anzeigen';