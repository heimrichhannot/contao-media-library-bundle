<?php

namespace HeimrichHannot\MediaLibraryBundle\Product;

use Contao\PageModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * Wrapper class for product models containing additional logic
 */
class Product
{
    private string $editLink;

    /**
     * @internal Do initialize this class directly. Use the ProductFactory instead.
     */
    public function __construct(
        private ProductModel $productModel,
        private Utils $utils,
    )
    {
    }

    public function __get(string $name)
    {
        return $this->productModel->{$name};
    }

    public function getModel(): ProductModel
    {
        return $this->productModel;
    }

    public function editLink(): ?string
    {
        if (!isset($this->editLink)) {
            $archive = ProductArchiveModel::findByPk($this->productModel->pid);
            if (!$archive) {
                return null;
            }
            if (!$archive->allowEdit) {
                return null;
            }
            $page = PageModel::findByPk($archive->editJumpTo);
            if (!$page) {
                return null;
            }
            $this->editLink = $this->utils->url()->addQueryStringParameterToUrl('edit='.$this->productModel->id, $page->getFrontendUrl());
        }

        return $this->editLink;
    }
}