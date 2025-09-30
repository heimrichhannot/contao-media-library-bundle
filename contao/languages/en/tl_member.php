<?php

use HeimrichHannot\MediaLibraryBundle\Security\Voter;

$lang = &$GLOBALS['TL_LANG']['tl_member'];

$lang['media_library_legend'] = 'Media library';

$lang['ml_archives'] = ['Media library archives', 'Select the media libraries for which the member should receive rights.'];
$lang['ml_archivep'] = ['Archive rights', 'Select the rights that the member should receive for the selected archives.'];

$lang['ml_archivep'][Voter::PERMISSION_CREATE] = 'Create items and upload files';
$lang['ml_archivep'][Voter::PERMISSION_EDIT] = 'Edit items and upload files';
$lang['ml_archivep'][Voter::PERMISSION_DELETE] = 'Delete items';
$lang['ml_archivep'][Voter::PERMISSION_DELETE_OWN] = 'Delete own items';
