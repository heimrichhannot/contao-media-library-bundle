<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\EventListener;

use Contao\StringUtil;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderEvent;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ListConfigContainer;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ListModifyQueryBuilderEventListener
{
    /**
     * @var ModelUtil
     */
    private $modelUtil;
    /**
     * @var Request
     */
    private $request;
    /**
     * @var DatabaseUtil
     */
    private $databaseUtil;

    public function __construct(ModelUtil $modelUtil, Request $request, DatabaseUtil $databaseUtil)
    {
        $this->modelUtil = $modelUtil;
        $this->request = $request;
        $this->databaseUtil = $databaseUtil;
    }

    public function modifyQueryBuilder(ListModifyQueryBuilderEvent $event)
    {
        $listConfig = $event->getListConfig();

        // set order according to additionalFilesOrder in tl_ml_product
        if (ListConfigContainer::SORTING_MODE_ML_ADDITIONAL_FILES === $listConfig->sortingMode) {
            $queryBuilder = $event->getQueryBuilder();

            if (null !== ($product = $this->modelUtil->findOneModelInstanceBy('tl_ml_product',
                    ['tl_ml_product.alias=?'], [$this->request->getGet('auto_item')])) && $product->addAdditionalFiles) {
                $order = StringUtil::deserialize($product->additionalFilesOrder, true);

                if (!empty($order)) {
                    if (null !== ($downloads = $this->findMultipleDownloadsByFileUuids($order)) && $downloads->numRows > 0) {
                        $ids = $downloads->fetchEach('id');

                        $queryBuilder->orderBy('FIELD(tl_ml_download.id,'.implode(',', array_map(function ($v) {
                            return '"'.$v.'"';
                        }, $ids)).')', ' ');
                    }
                }
            }
        }
    }

    public function findMultipleDownloadsByFileUuids($uuids, array $options = [])
    {
        if (empty($uuids) || !\is_array($uuids)) {
            return null;
        }

        $t = 'tl_ml_download';

        foreach ($uuids as $k => $v) {
            // Convert UUIDs to binary
            if (\Validator::isStringUuid($v)) {
                $v = \StringUtil::uuidToBin($v);
            }

            $uuids[$k] = "UNHEX('".bin2hex($v)."')";
        }

        if (!isset($options['order'])) {
            $options['order'] = "$t.file!=".implode(", $t.file!=", $uuids);
        }

        return $this->databaseUtil->findResultsBy('tl_ml_download', ["$t.file IN(".implode(',', $uuids).')'], null, $options);
    }
}
