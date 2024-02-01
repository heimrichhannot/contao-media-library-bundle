<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\PageModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\MediaLibraryBundle\Product\Product;
use HeimrichHannot\MediaLibraryBundle\Product\ProductFactory;
use HeimrichHannot\MediaLibraryBundle\Security\ProductVoter;
use HeimrichHannot\ReaderBundle\Event\ReaderBeforeRenderEvent;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReaderBundleListener implements EventSubscriberInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
        private ProductFactory $productFactory,
        private Security $security,
    )
    {
    }

    public function onBeforeRenderEvent(ReaderBeforeRenderEvent $event): void
    {
        $item = $event->getItem();
        if ($item->getDataContainer() !== 'tl_ml_product') {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $productModel = ProductModel::findByPk($item->getRawValue('id'));

        $this->runIfDeleteAction($request, $productModel);

        $product = $this->productFactory->createFromModel($productModel);

        $templateData = $event->getTemplateData();
        if ($this->security->isGranted(ProductVoter::PERMISSION_EDIT, $productModel)) {
            $templateData['editLink'] = $product->editLink();
        }
        if ($this->security->isGranted(ProductVoter::PERMISSION_DELETE, $productModel)) {
            $templateData['deleteLink'] = $product->deleteLink();
        }
        $event->setTemplateData($templateData);
    }

    private function runIfDeleteAction(Request $request, ProductModel $productModel)
    {
        $archiveId = $productModel->pid;
        $deleted = $this->productFactory->getProductHelper()->runIfDeleteAction();

        if (!$deleted && ($request->isMethod(Request::METHOD_DELETE) || 'DELETE' === $request->request->get('_method'))) {
            $deleted = $this->productFactory->getProductHelper()->deleteProduct($productModel);
        }

        if ($deleted) {
            $archive = ProductArchiveModel::findByPk($archiveId);
            if ($archive && $archive->redirectAfterDelete) {
                $page = PageModel::findByPk($archive->redirectAfterDelete);
                if ($page) {
                    $request->getSession()->getFlashBag()->add('success', $this->translator->trans('huh.mediaLibrary.product.delete.success', ['title' => $productModel->title]));
                    throw new RedirectResponseException(throw new RedirectResponseException($page->getAbsoluteUrl()));
                }
            }
        }
    }

    public static function getSubscribedEvents()
    {
        $events = [];
        if (class_exists(ReaderBeforeRenderEvent::class)) {
            $events[ReaderBeforeRenderEvent::NAME] = 'onBeforeRenderEvent';
        }
        return $events;
    }
}
