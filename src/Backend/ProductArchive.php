<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 16.03.18
 * Time: 10:57
 */

namespace HeimrichHannot\MediaLibraryBundle\Backend;


use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;
use HeimrichHannot\MediaLibraryBundle\Registry\ProductArchiveRegistry;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use League\Uri\Schemes\Data;

class ProductArchive
{
    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;
    
    /**
     * @var ProductArchiveRegistry
     */
    protected $productArchiveRegistry;
    
    /**
     * @var ModelUtil
     */
    protected $modelUtil;
    
    /**
     * @var DcaUtil
     */
    protected $dcaUtil;
    
    public function __construct(
        ContaoFrameworkInterface $framework,
        ProductArchiveRegistry $productArchiveRegistry,
        ModelUtil $modelUtil,
        DcaUtil $dcaUtil
    ) {
        $this->framework              = $framework;
        $this->productArchiveRegistry = $productArchiveRegistry;
        $this->modelUtil              = $modelUtil;
        $this->dcaUtil                = $dcaUtil;
    }
    
    /**
     * get fields from tl_ml_product
     *
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function getPaletteFields(DataContainer $dc)
    {
        return System::getContainer()
            ->get('huh.utils.dca')
            ->getFields('tl_ml_product',
                ['inputTypes' => ['text', 'textarea', 'select', 'multifileupload', 'checkbox', 'tagsinput'], 'localizeLabels' => false]);
    }
    
    /**
     * get image sizes
     *
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function getImageSizes(DataContainer $dc)
    {
        $user       = BackendUser::getInstance();
        $imageSizes = System::getContainer()->get('contao.image.image_sizes')->getOptionsForUser($user);
        
        return $imageSizes['image_sizes'];
    }
    
    /**
     * get fields that have been selected in palette
     *
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function getFieldsForTag(DataContainer $dc)
    {
        return deserialize($dc->activeRecord->palette, true);
    }
}