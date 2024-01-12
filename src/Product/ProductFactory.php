<?php

namespace HeimrichHannot\MediaLibraryBundle\Product;

use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ProductFactory
{
    public function __construct(private Utils $utils)
    {
    }

    public function createFromModel(ProductModel $productModel): Product
    {
        return new Product($productModel, $this->utils);
    }
}