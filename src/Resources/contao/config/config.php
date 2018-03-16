<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 13.03.18
 * Time: 11:59
 */

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], count($GLOBALS['BE_MOD']['content']), [
    'media-library' => [
        'tables' => ['tl_ml_product_archive', 'tl_ml_product', 'tl_ml_download'],
    ],
]);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_ml_product_archive'] = 'HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel';
$GLOBALS['TL_MODELS']['tl_ml_product']         = 'HeimrichHannot\MediaLibraryBundle\Model\ProductModel';
$GLOBALS['TL_MODELS']['tl_ml_download']        = 'HeimrichHannot\MediaLibraryBundle\Model\DownloadModel';