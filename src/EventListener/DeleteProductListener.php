<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener;

use Contao\CoreBundle\Exception\InternalServerErrorHttpException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Model;
use Contao\PageModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\ReaderBundle\Event\ReaderBeforeRenderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class DeleteProductListener
{
    public function __construct(
        private RequestStack $requestStack
    )
    {
    }

    #[AsEventListener('huh.reader.event.reader_before_render')]
    public function deleteProduct(ReaderBeforeRenderEvent $event): void
    {
        $item = $event->getItem();

        if ($item->getDataContainer() !== 'tl_ml_product') {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = Model::getClassFromTable('tl_ml_product');
        $product = $modelClass::findByPk($item->getRawValue('id'));
        if ($product === null) {
            throw new BadRequestHttpException('Invalid product id');
        }

        if (!$product->delete()) {
            throw new InternalServerErrorHttpException('Could not delete product');
        }

        /** @var ProductArchiveModel|null $productArchive */
        $productArchive = $product->getRelated('pid');

        if ($productArchive === null) {
            throw new InternalServerErrorHttpException('Product archive not found');
        }

        if (!$productArchive->includeDelete) {
            throw new MethodNotAllowedHttpException([Request::METHOD_DELETE], 'Delete not allowed');
        }

        $pageId = $productArchive->redirectAfterDelete;
        $page = PageModel::findByPk($pageId);

        if ($page === null) {
            throw new InternalServerErrorHttpException('No redirect page found');
        }

        throw new RedirectResponseException($page->getAbsoluteUrl());
    }
}