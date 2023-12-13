<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener;

use Contao\CoreBundle\Exception\InternalServerErrorHttpException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Input;
use Contao\Model;
use Contao\PageModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\ReaderBundle\Event\ReaderBeforeRenderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteProductListener
{
    private RequestStack $requestStack;

    private TranslatorInterface $translator;

    private SessionInterface $session;

    public function __construct(
        RequestStack $requestStack,
        TranslatorInterface $translator,
        SessionInterface $session
    )
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->session = $session;
    }

    #[AsEventListener('huh.reader.event.reader_before_render')]
    public function deleteProduct(ReaderBeforeRenderEvent $event): void
    {
        $item = $event->getItem();

        if ($item->getDataContainer() !== 'tl_ml_product') {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();

        if (!$request->isMethod(Request::METHOD_DELETE)
            && Input::post('_method') !== 'DELETE')
        {
            return;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = Model::getClassFromTable('tl_ml_product');
        $product = $modelClass::findByPk($item->getRawValue('id'));
        if ($product === null) {
            throw new BadRequestHttpException('Invalid product id');
        }

        /** @var ProductArchiveModel|null $productArchive */
        $productArchive = $product->getRelated('pid');

        if ($productArchive === null) {
            throw new InternalServerErrorHttpException('Product archive not found');
        }

        if (!$productArchive->includeDelete) {
            throw new MethodNotAllowedHttpException(
                [Request::METHOD_GET, Request::METHOD_POST, Request::METHOD_HEAD],
                'Delete not allowed'
            );
        }

        if (!$product->delete()) {
            throw new InternalServerErrorHttpException('Could not delete product');
        }

        $pageId = $productArchive->redirectAfterDelete;
        $page = PageModel::findByPk($pageId);

        if ($page === null) {
            throw new InternalServerErrorHttpException('No redirect page found');
        }

        $this->session
            ->getFlashBag()
            ->add('success', $this->translator->trans('huh.mediaLibrary.product.delete.success', ['title' => $product->title]))
        ;

        throw new RedirectResponseException($page->getAbsoluteUrl());
    }
}