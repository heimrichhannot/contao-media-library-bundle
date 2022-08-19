<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Manager;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Contao\System;
use HeimrichHannot\AjaxBundle\Response\ResponseData;
use HeimrichHannot\AjaxBundle\Response\ResponseError;
use HeimrichHannot\AjaxBundle\Response\ResponseSuccess;
use HeimrichHannot\UtilsBundle\File\FileUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

class AjaxManager
{
    public const MEDIA_LIBRARY_XHR_GROUP = 'mediaLibrary';

    public const MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS = 'showOptionsModal';

    public const MEDIA_LIBRARY_ARGUMENTS_OPTIONS = 'options';

    /**
     * @var ContaoFrameworkInterface
     */
    protected $framework;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var FileUtil
     */
    protected $fileUtil;

    /**
     * @var \HeimrichHannot\AjaxBundle\Manager\AjaxManager
     */
    protected $ajax;

    public function __construct(ContaoFrameworkInterface $framework, TranslatorInterface $translator, FileUtil $fileUtil, \HeimrichHannot\AjaxBundle\Manager\AjaxManager $ajax)
    {
        $this->framework = $framework;
        $this->translator = $translator;
        $this->fileUtil = $fileUtil;
        $this->ajax = $ajax;
    }

    public function ajaxActions()
    {
        $GLOBALS['AJAX'][static::MEDIA_LIBRARY_XHR_GROUP] = [
            'actions' => [
                static::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS => [
                    'arguments' => [
                        static::MEDIA_LIBRARY_ARGUMENTS_OPTIONS,
                    ],
                    'optional' => [],
                ],
            ],
        ];

        $this->ajax->runActiveAction(static::MEDIA_LIBRARY_XHR_GROUP, static::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS, $this);
    }

    public function showOptionsModal($options)
    {
        if (!$options) {
            return new ResponseError();
        }

        if (!\is_array($options) && empty($options = json_decode($options))) {
            return new ResponseError();
        }

        System::loadLanguageFile('tl_ml_product');
        $template = new FrontendTemplate('options_modal');

        $template->options = $options;
        $template->label = $this->translator->trans('huh.mediaLibrary.options.label');
        $template->class = 'media-library-options';

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['modal' => $template->parse(), 'config' => $this->getConfig()]));

        return $response;
    }

    protected function getPathsFromOptions(array $options)
    {
        $files = [];

        foreach ($options as $option) {
            if (null === ($path = $this->fileUtil->getPathFromUuid($option->uuid))) {
                continue;
            }

            $files[$path] = $option->title;
        }

        return $files;
    }

    protected function getConfig()
    {
        return json_encode([
            'btnLabel' => $this->translator->trans('huh.mediaLibrary.btn.label'),
            'btnClass' => $this->translator->trans('huh.mediaLibrary.btn.class'),
            'alertTitle' => $this->translator->trans('huh.mediaLibrary.alert.download.title'),
            'abortLabel' => $this->translator->trans('huh.mediaLibrary.btn.abort.label'),
        ]);
    }
}
