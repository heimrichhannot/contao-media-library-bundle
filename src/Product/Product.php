<?php

namespace HeimrichHannot\MediaLibraryBundle\Product;

use Contao\PageModel;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\UtilsBundle\Util\Utils;

/**
 * Wrapper class for product models containing additional logic
 */
class Product
{
    public const PARAMETER_EDIT = 'edit';
    public const PARAMETER_DELETE = 'deleteProduct';

    private ?string $editLink;
    private ?string $deleteLink;

    /**
     * @internal Do initialize this class directly. Use the ProductFactory instead.
     */
    public function __construct(
        private ItemModel $productModel,
        private Utils     $utils,
    )
    {
    }

    public function __get(string $name)
    {
        return $this->productModel->{$name};
    }

    public function getModel(): ItemModel
    {
        return $this->productModel;
    }

    public function editLink(): ?string
    {
        if (!isset($this->editLink)) {
            $archive = ArchiveModel::findByPk($this->productModel->pid);
            if (!$archive || !$archive->allowEdit || !($page = PageModel::findByPk($archive->editJumpTo))) {
                $this->editLink = null;
                return null;
            }

            $this->editLink = $this->utils->url()->addQueryStringParameterToUrl(static::PARAMETER_EDIT.'='.$this->productModel->id, $page->getFrontendUrl());
        }

        return $this->editLink;
    }

    public function deleteLink(): ?string
    {
        if (!isset($this->deleteLink)) {
            $archive = ArchiveModel::findByPk($this->productModel->pid);
            if (!$archive || !$archive->includeDelete) {
                $this->deleteLink = null;
                return null;
            }

            $this->deleteLink = $this->utils->url()->addQueryStringParameterToUrl(static::PARAMETER_DELETE.'='.$this->productModel->id);
        }

        return $this->deleteLink;
    }


}