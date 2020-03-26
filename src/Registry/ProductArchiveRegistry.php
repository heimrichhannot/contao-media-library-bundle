<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ProductArchiveRegistry
{
    /** @var ContaoFrameworkInterface */
    protected $framework;

    /** @var ModelUtil */
    protected $modelUtil;

    public function __construct(ContaoFrameworkInterface $framework, ModelUtil $modelUtil)
    {
        $this->framework = $framework;
        $this->modelUtil = $modelUtil;
    }

    /**
     * Adapter function for the model's findBy method.
     *
     * @param mixed $column
     * @param mixed $value
     *
     * @return \Contao\Model\Collection|ProductArchiveModel|null
     */
    public function findBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_ml_product_archive',
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
     *
     * @return \Contao\Model\Collection|ProductArchiveModel|null
     */
    public function findOneBy($column, $value, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_ml_product_archive',
            $column,
            $value,
            $options
        );
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $pk
     *
     * @return \Contao\Model\Collection|ProductArchiveModel|null
     */
    public function findByPk($pk, array $options = [])
    {
        return $this->modelUtil->findModelInstanceByPk(
            'tl_ml_product_archive',
            $pk,
            $options
        );
    }
}
