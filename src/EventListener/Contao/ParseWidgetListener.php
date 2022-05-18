<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FilesModel;
use Contao\Image;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;
use HeimrichHannot\UtilsBundle\Util\Utils;
use HeimrichHannot\VmdBundle\Model\MlProductModel;

/**
 * @Hook("parseWidget")
 */
class ParseWidgetListener
{
    /**
     * @var Utils
     */
    private $utils;
    /**
     * @var ModelUtil
     */
    private $modelUtil;

    public function __construct(Utils $utils, ModelUtil $modelUtil)
    {
        $this->utils = $utils;
        $this->modelUtil = $modelUtil;
    }

    public function __invoke(string $buffer, Widget $widget): string
    {
        if (!$this->utils->container()->isBackend() || !('tl_ml_product' == $widget->strTable) || !('copyright' == $widget->name)) {
            return $buffer;
        }

        $product = MlProductModel::findByPk((int) $widget->currentRecord);

        if (!$product) {
            return $buffer;
        }

        $uuid = $product->file;

        if (\is_array(StringUtil::deserialize($uuid))) {
            $uuid = array_values(StringUtil::deserialize($uuid, true))[0] ?? null;
        }

        if (!$uuid) {
            return '';
        }

        /** @var FilesModel|null $fileModel */
        $fileModel = $this->modelUtil->callModelMethod('tl_files', 'findByUuid', $uuid);

        if (!$fileModel) {
            return '';
        }

        $title = sprintf($GLOBALS['TL_LANG']['tl_files']['editFile'], $fileModel->name);

        $href = System::getContainer()->get('router')->generate(
            'contao_backend',
            ['do' => 'files', 'table' => 'tl_files', 'act' => 'edit', 'id' => $fileModel->path, 'popup' => '1', 'nb' => '1', 'rt' => REQUEST_TOKEN]
        );

        return $buffer.' <a href="'.StringUtil::specialcharsUrl($href).'" title="'.StringUtil::specialchars($title).'" onclick="Backend.openModalIframe({\'title\':\''.StringUtil::specialchars(str_replace("'", "\\'", $title)).'\',\'url\':this.href});return false">'.Image::getHtml('alias.svg', $title).'</a>';

//        return $buffer;
    }
}
