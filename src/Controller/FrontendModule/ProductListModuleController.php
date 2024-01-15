<?php

namespace HeimrichHannot\MediaLibraryBundle\Controller\FrontendModule;

use Contao\BackendUser;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\FrontendTemplate;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\MediaLibraryBundle\Product\ProductFactory;
use HeimrichHannot\MediaLibraryBundle\Product\ProductHelper;
use HeimrichHannot\MediaLibraryBundle\Security\ProductVoter;
use HeimrichHannot\UtilsBundle\Util\Utils;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

/**
 * @FrontendModule(ProductListModuleController::TYPE, category="media_library")
 */
class ProductListModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'ml_product_list';

    public function __construct(
        private ProductFactory $productFactory,
        private ProductHelper $productHelper,
        private Utils $utils,
    ){}


    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        if ($this->productHelper->runIfDeleteAction()) {
            throw new RedirectResponseException($this->utils->url()->removeQueryStringParameterFromUrl('delete'));
        }

        $archives = ProductArchiveModel::findMultipleByIds(StringUtil::deserialize($model->ml_archives, true));
        if (!$archives) {
            return $template->getResponse();
        }

        $options = [
            'order' => 'tl_ml_product.title',
        ];

        if ($model->perPage) {
            $options['limit'] = $model->perPage;
        }

        $productCollection = ProductModel::findBy(
            [
                'tl_ml_product.pid IN ('.implode(',', $archives->fetchEach('id')).')',
                'tl_ml_product.published=?',
            ],
            [
                '1',
            ],
            $options
        );

        if (!$productCollection) {
            return $template->getResponse();
        }

        $products = [];
        foreach ($productCollection as $productModel) {
            $product = $this->productFactory->createFromModel($productModel);
            $template = new FrontendTemplate('ml_product_short');
            $template->title = $product->title;
            $template->product = $product;
            $template->editLink = $product->editLink();
            $template->deleteLink = $product->deleteLink();
            $products[] = $template->parse();
        }

        $template->products = $products;

        return $template->getResponse();
    }
}