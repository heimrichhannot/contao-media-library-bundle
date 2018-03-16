<?php

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Model;

/**
 * @property string $title
 * @property string $category
 * @property string $uploadedFiles
 * @property text   $text
 * @property string $tag
 */
class ProductModel extends Model
{
    protected static $strTable = 'tl_ml_product';
}