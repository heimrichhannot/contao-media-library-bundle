<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\EventListener;

use Contao\Database;
use Contao\StringUtil;
use HeimrichHannot\ListBundle\Event\ListModifyQueryBuilderEvent;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ListConfigContainer;
use HeimrichHannot\RequestBundle\Component\HttpFoundation\Request;
use HeimrichHannot\UtilsBundle\Database\DatabaseUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: "huh.list.event.list_modify_query_builder")]
class ListModifyQueryBuilderEventListener
{
    public function __construct(
        private readonly Utils $utils,
        private readonly Request $request,
        private readonly DatabaseUtil $databaseUtil
    ) {}

    public function __invoke(ListModifyQueryBuilderEvent $event): void
    {
        $listConfig = $event->getListConfig();

        // set order according to additionalFilesOrder in tl_ml_product
        if (ListConfigContainer::SORTING_MODE_ML_ADDITIONAL_FILES !== $listConfig->sortingMode)
            // TODO: this currently is always true, as the comparison above is of incompatible types
        {
            return;
        }

        $queryBuilder = $event->getQueryBuilder();

        $product = $this->utils->model()
            ->findOneModelInstanceBy(
                'tl_ml_product',
                ['tl_ml_product.alias=?'],
                [$this->request->getGet('auto_item')]
            );

        if ($product === null || !$product->addAdditionalFiles) {
            return;
        }

        $order = StringUtil::deserialize($product->additionalFilesOrder, true);
        if (empty($order)) {
            return;
        }

        $downloads = $this->findMultipleDownloadsByFileUuids($order);
        if ($downloads === null || $downloads->numRows < 1) {
            return;
        }

        $ids = $downloads->fetchEach('id');

        $queryBuilder->orderBy('FIELD(tl_ml_download.id,'.implode(',', array_map(function ($v) {
            return '"'.$v.'"';
        }, $ids)).')', ' ');
    }

    public function findMultipleDownloadsByFileUuids($uuids, array $options = []): Database\Statement|Database\Result|null
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
