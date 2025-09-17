<?php

namespace HeimrichHannot\MediaLibraryBundle\Product;

use Contao\Input;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use HeimrichHannot\MediaLibraryBundle\Security\ProductVoter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Security;

class ProductHelper
{
    public function __construct(
        private Security $security,
        private RequestStack $requestStack,
    )
    {
    }

    public function runIfDeleteAction(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request && $request->query->has(Product::PARAMETER_DELETE) && ($product = ItemModel::findByPk($request->query->get(Product::PARAMETER_DELETE)))) {
            return $this->deleteProduct($product);
        }

        return false;
    }

    public function deleteProduct(ItemModel $product): bool
    {
        if (!$this->security->isGranted(ProductVoter::PERMISSION_DELETE, $product)) {
            throw new AccessDeniedHttpException();
        }

        return (bool) $product->delete();
    }
}