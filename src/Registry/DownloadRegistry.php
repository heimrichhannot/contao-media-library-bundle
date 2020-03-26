<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Registry;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Model;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use Model\Collection;

class DownloadRegistry
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
     * @return Collection|Model|null
     */
    public function findBy(array $columns, array $values, array $options = [])
    {
        foreach ($columns as &$column) {
            $column = 'tl_ml_download.'.$column;
        }

        return $this->modelUtil->findModelInstancesBy(
            'tl_ml_download',
            $columns,
            $values,
            $options
        );
    }

    /**
     * Adapter function for the model's findOneBy method.
     *
     * @param mixed $column
     * @param mixed $value
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
     * @return \Contao\Model\Collection|DownloadModel|null
     */
    public function findByPid(int $pid, array $options = [])
    {
        return $this->modelUtil->findModelInstancesBy(
            'tl_ml_download',
            ['tl_ml_download.pid=?'],
            [$pid],
            $options
        );
    }

    /**
     * Adapter function for the model's findByPk method.
     *
     * @param mixed $pid
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
