<?php

namespace HeimrichHannot\MediaLibraryBundle\Controller\FrontendModule;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Environment;
use Contao\FilesModel;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\ModuleModel;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\Template;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\MediaLibraryBundle\Product\Product;
use HeimrichHannot\MediaLibraryBundle\Product\ProductFactory;
use HeimrichHannot\MediaLibraryBundle\Security\ProductVoter;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
        private Utils $utils,
        private Security $security,
        private ParameterBagInterface $parameterBag,
    ){}


    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        if ($this->productFactory->getProductHelper()->runIfDeleteAction()) {
            throw new RedirectResponseException($this->utils->url()->removeQueryStringParameterFromUrl('delete'));
        }

        $archives = ProductArchiveModel::findMultipleByIds(StringUtil::deserialize($model->ml_archives, true));
        if (!$archives) {
            return $template->getResponse();
        }

        $limit = null;
        $offset = 0;

        // Maximum number of items
        if ($model->numberOfItems > 0)
        {
            $limit = $model->numberOfItems;
        }

        $columns = [
            'tl_ml_product.pid IN (' . implode(',', $archives->fetchEach('id')) . ')',
            'tl_ml_product.published=?',
        ];

        $total = ProductModel::countBy($columns, ['1']);


        // Split the results
        if ($model->perPage > 0 && (!isset($limit) || $model->numberOfItems > $model->perPage))
        {
            // Adjust the overall limit
            if (isset($limit))
            {
                $total = min($limit, $total);
            }

            // Get the current page
            $id = 'page_m' . $model->id;
            $page = Input::get($id) ?? 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total/$model->perPage), 1))
            {
                throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
            }

            // Set limit and offset
            $limit = $model->perPage;
            $offset += (max($page, 1) - 1) * $model->perPage;

            // Overall limit
            if ($offset + $limit > $total)
            {
                $limit = $total - $offset;
            }

            // Add the pagination menu
            $objPagination = new Pagination($total, $model->perPage, Config::get('maxPaginationLinks'), $id);
            $template->pagination = $objPagination->generate("\n  ");
        }

        $options = [
            'order' => 'tl_ml_product.title',
            'offset' => $offset,
            'limit' => $limit,
        ];

        $productCollection = ProductModel::findBy($columns, ['1',], $options);

        if (!$productCollection) {
            return $template->getResponse();
        }

        $products = [];
        foreach ($productCollection as $productModel) {
            $product = $this->productFactory->createFromModel($productModel);
            $productTemplate = new FrontendTemplate('ml_product_short');
            $productTemplate->title = $product->title;
            $productTemplate->product = $product;

            $this->addImage($product, $productTemplate, $model);
            if ($this->security->isGranted(ProductVoter::PERMISSION_EDIT, $productModel)) {
                $productTemplate->editLink = $product->editLink();
            }
            if ($this->security->isGranted(ProductVoter::PERMISSION_DELETE, $productModel)) {
                $productTemplate->deleteLink = $product->deleteLink();
            }
            $products[] = $productTemplate->parse();
        }

        $template->products = $products;

        return $template->getResponse();
    }

    private function addImage(Product $product, Template $template, ModuleModel $model)
    {
        $fileModel = FilesModel::findByUuid($product->file);

        if ($fileModel !== null && is_file($this->parameterBag->get('kernel.project_dir') . '/' . $fileModel->path))
        {
            // Do not override the field now that we have a model registry (see #6303)
            $arrArticle = $product->getModel()->row();

            // Override the default image size
            if ($model->imgSize)
            {
                $size = StringUtil::deserialize($model->imgSize);

                if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]) || ($size[2][0] ?? null) === '_')
                {
                    $arrArticle['size'] = $model->imgSize;
                }
            }

            $arrArticle['singleSRC'] = $fileModel->path;
            Controller::addImageToTemplate($template, $arrArticle, null, null, $fileModel);

            // Link to the news article if no image link has been defined (see #30)
            if (!$template->fullsize && !$template->imageUrl)
            {
                // Unset the image title attribute
                $picture = $template->picture;
                unset($picture['title']);
                $template->picture = $picture;

                // Link to the news article
                $template->href = $template->link;
                $template->linkTitle = StringUtil::specialchars(sprintf($GLOBALS['TL_LANG']['MSC']['readMore'], $product->headline), true);

                // If the external link is opened in a new window, open the image link in a new window, too (see #210)
                if ($template->source == 'external' && $template->target && strpos($template->attributes, 'target="_blank"') === false)
                {
                    $template->attributes .= ' target="_blank"';
                }
            }
        }
    }
}