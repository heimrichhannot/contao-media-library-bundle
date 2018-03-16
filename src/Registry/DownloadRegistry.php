<?php
/**
 * Created by PhpStorm.
 * User: mkunitzsch
 * Date: 14.03.18
 * Time: 16:36
 */

namespace HeimrichHannot\MediaLibraryBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class DownloadRegistry
{
    /** @var ContaoFrameworkInterface */
    protected $framework;
    
    /** @var ModelUtil */
    protected $modelUtil;
    
    /** @var DcaUtil */
    protected $dcaUtil;
    
    public function __construct(ContaoFrameworkInterface $framework, ModelUtil $modelUtil, DcaUtil $dcaUtil)
    {
        $this->framework = $framework;
        $this->modelUtil = $modelUtil;
        $this->dcaUtil   = $dcaUtil;
    }
    
    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|DownloadModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_ml_download',
            $column,
            $value,
            $options
        );
    }
    
    /**
     * Adapter function for the model's findOneBy method.
     *
     * @param mixed $column
     * @param mixed $value
     * @param array $options
     *
     * @return \Contao\Model\Collection|DownloadModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_ml_download',
            $column,
            $value,
            $options
        );
    }
    
    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $pk
     * @param array $options
     *
     * @return \Contao\Model\Collection|DownloadModel|null
     */
    public function findByPk($pk, array $options = [])
    {
        return $this->modelUtil->findModelInstanceByPk(
            'tl_ml_download',
            $pk,
            $options
        );
    }
    
    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $pid
     * @param array $options
     *
     * @return \Contao\Model\Collection|DownloadModel|null
     */
    public function findGeneratedImages($pid, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_ml_download',
            ['tl_ml_download.pid=?', 'tl_ml_download.author=?'],
            [$pid, 0],
            $options
        );
    }
}