<?php

namespace HeimrichHannot\MediaLibraryBundle\Product;

use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

class ProductFactory
{
    public function __construct(
        private Utils $utils,
        private ProductHelper $productHelper
    )
    {
    }

    public function createFromModel(ItemModel $productModel): Product
    {
        return new Product($productModel, $this->utils);
    }

    public function getProductHelper(): ProductHelper
    {
        return $this->productHelper;
    }
}