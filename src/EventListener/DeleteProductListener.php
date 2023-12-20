<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener;

use Contao\CoreBundle\Exception\InternalServerErrorHttpException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\MemberModel;
use Contao\Model;
use Contao\PageModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\ReaderBundle\Event\ReaderBeforeRenderEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeleteProductListener
{
    private RequestStack $requestStack;

    private TranslatorInterface $translator;

    private SessionInterface $session;

    private TokenChecker $tokenChecker;

    public function __construct(
        RequestStack $requestStack,
        TranslatorInterface $translator,
        SessionInterface $session,
        TokenChecker $tokenChecker,
    )
    {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->session = $session;
        $this->tokenChecker = $tokenChecker;
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

        if (!$this->tokenChecker->hasFrontendUser()) {
            throw new AccessDeniedHttpException('Invalid token');
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = Model::getClassFromTable('tl_ml_product');
        /** @var ?ProductModel $product */
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

        $member = MemberModel::findBy('username', $this->tokenChecker->getFrontendUsername());
        if ($member === null) {
            throw new InternalServerErrorHttpException('Member not found');
        }

        if (!$product->memberCanDelete($member)) {
            throw new UnauthorizedHttpException('Not authorized to delete this product');
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