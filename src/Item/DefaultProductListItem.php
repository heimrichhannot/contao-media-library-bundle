<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Item;

use Contao\Controller;
use Contao\FrontendTemplate;
use Contao\FrontendUser;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\Validator;
use HeimrichHannot\ListBundle\Item\DefaultItem;

class DefaultProductListItem extends DefaultItem
{
    public function getDownloadLink()
    {
        if (!$this->checkPermission()) {
            return null;
        }

        [$downloads, $hasOptions] = $this->getDownloadItems();

        if (empty($downloads)) {
            return null;
        }

        System::loadLanguageFile('tl_ml_product');

        $template = new FrontendTemplate('download_action');

        $template->link = $GLOBALS['TL_LANG']['tl_ml_product']['downloadLink'];
        $template->title = sprintf($GLOBALS['TL_LANG']['tl_ml_product']['downloadLink'], $this->getRawValue('title'));

        if (!$hasOptions) {
            $file = Input::get('file', true);

            // Send the file to the browser (see #4632 and #8375)
            if ($file && (!isset($_GET['lid']) || Input::get('lid') == $this->getIdOrAlias()))
            {
                if ($file == $downloads)
                {
                    Controller::sendFileToBrowser($file);
                }
            }

            $template->file = System::getContainer()->get('huh.utils.url')->addQueryString('file='.$downloads.'&lid='.$this->getIdOrAlias());

            return $template->parse();
        }

        $template->options = $downloads;
        $template->action = System::getContainer()->get('huh.ajax.action')->generateUrl(\HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_XHR_GROUP, \HeimrichHannot\MediaLibraryBundle\Manager\AjaxManager::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS);

        return $template->parse();
    }

    public function getProductArchive(): ?Model
    {
        return $archive = System::getContainer()->get('huh.utils.model')->findModelInstanceByPk('tl_ml_product_archive', $this->getRawValue('pid'));
    }

    protected function getDownloadItems(string $fileField = 'uploadedFiles')
    {
        if (null !== ($downloads = System::getContainer()->get('huh.utils.model')->findModelInstancesBy('tl_ml_download', ['tl_ml_download.pid=?'], [$this->getRawValue('id')]))) {
            $items = [];

            foreach ($downloads as $download) {
                $deserializedFile = StringUtil::deserialize($download->file);

                $uuid = Validator::isUuid($download->file) ? $download->file : reset($deserializedFile);

                $items[] = [
                    'label' => urlencode($download->title),
                    'file' => System::getContainer()->get('huh.utils.file')->getPathFromUuid($uuid, false),
                    'uuid' => StringUtil::binToUuid($uuid),
                ];
            }

            if (1 == \count($items)) {
                return [$items[0]['file'], false];
            }

            return [json_encode($items, \JSON_UNESCAPED_SLASHES), true];
        }

        $downloads = StringUtil::deserialize($this->getRawValue($fileField), true);

        if (empty($downloads)) {
            return [];
        }

        if (1 === (\is_array($downloads) || $downloads instanceof \Countable ? \count($downloads) : 0)) {
            return [System::getContainer()->get('huh.utils.file')->getPathFromUuid($downloads[0]), false];
        }

        return [$this->getOptionsFromArray($downloads), true];
    }

    protected function getOptionsFromArray($downloadItems)
    {
        $options = [];

        foreach ($downloadItems as $item) {
            $options[] = [
                'title' => $this->getRawValue('title'),
                'uuid' => $item,
            ];
        }

        return $options;
    }

    protected function checkPermission(): bool
    {
        if (null === ($archive = $this->getProductArchive())) {
            return false;
        }

        $permitted = true;

        if ($archive->protected) {
            $permitted = $this->checkUserPermission($archive);
        }

        return $permitted;
    }

    protected function checkUserPermission(Model $archive): bool
    {
        if (null === ($user = FrontendUser::getInstance())) {
            return false;
        }

        if (!array_intersect(StringUtil::deserialize($archive->groups, true), StringUtil::deserialize($user->groups, true))) {
            return false;
        }

        return true;
    }
}
