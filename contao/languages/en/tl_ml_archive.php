<?php

use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;

$lang = &$GLOBALS['TL_LANG'][ArchiveModel::getTable()];

/**
 * Fields
 */
$lang['title'] = ['Title', 'Please enter a title.'];
$lang['published'] = ['Publish product archive', 'Make the product archive publicly visible on the website.'];
$lang['start'] = ['Show from', 'Do not publish the product archive on the website before this date.'];
$lang['stop'] = ['Show until', 'Unpublish the product archive on the website after this date.'];
$lang['tstamp'] = ['Revision date', ''];
$lang['palette'][0] = 'Fields for the product palette';
$lang['palette'][1] = 'Select the fields that should be added to the palette of products in this archive.';

$lang['jumpTo'][0]                           = 'Redirect page';
$lang['jumpTo'][1]                           = 'Select the product reader page to which the user should be redirected after clicking on a product.';
$lang['enableDelete'][0]                    = 'Products can be deleted';
$lang['enableDelete'][1]                    = 'Select this option if you want to allow users to delete the product.';
$lang['deleteJumpTo'][0]              = 'Redirect page after deletion';
$lang['deleteJumpTo'][1]              = 'Select the page to which the user should be redirected after deleting the product.';
$lang['groupsCanDeleteOwn'][0]               = 'Delete own products (Obsolete, please set in member(group) settings)';
$lang['groupsCanDeleteOwn'][1]               = 'Select the member groups here that are allowed to delete their own products.';
$lang['groupsCanDeleteAll'][0]               = 'Delete all products (Obsolete, please set in member(group) settings)';
$lang['groupsCanDeleteAll'][1]               = 'Select the member groups here that are allowed to delete all products.';
$lang['enableCreate'][0] = 'Products can be created';
$lang['enableCreate'][1] = 'Select this option if you want to allow users to create new products in the frontend.';
$lang['enableEdit'][0] = 'Products can be edited';
$lang['enableEdit'][1] = 'Select this option if you want to allow users to edit the product in the frontend.';
$lang['editJumpTo'][0] = 'Edit page';
$lang['editJumpTo'][1] = 'Select the page on which the form for editing the product is located.';

/**
 * Legends
 */
$lang['general_legend'] = 'General settings';
$lang['advanced_legend'] = 'Advanced settings';
$lang['edit_legend'] = 'Edit settings';
$lang['protect_legend'] = 'Access protection';
$lang['publish_legend'] = 'Publish settings';

/**
 * Buttons
 */
$lang['new'] = ['New product archive', 'product archive create'];
