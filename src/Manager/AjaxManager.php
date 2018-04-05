<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
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

class AjaxManager
{
    const MEDIA_LIBRARY_XHR_GROUP = 'mediaLibrary';

    const MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS = 'showOptionsModal';

    const MEDIA_LIBRARY_ARGUMENTS_OPTIONS = 'options';

    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
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

        System::getContainer()->get('huh.ajax')->runActiveAction(static::MEDIA_LIBRARY_XHR_GROUP, static::MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS, $this);
    }

    public function showOptionsModal($options)
    {
        if (!$options) {
            return new ResponseError();
        }

        System::loadLanguageFile('tl_ml_product');
        $template = new FrontendTemplate('options_modal');

        $template->options = $this->getPathsFromOptions($options);
        $template->class = 'media-library-options';
        $template->link = $GLOBALS['TL_LANG']['tl_ml_product']['downloadLink'];

        $wrapper = new FrontendTemplate('modal_wrapper');

        $wrapper->title = $GLOBALS['TL_LANG']['tl_ml_product']['downloadItem'];
        $wrapper->content = $template->parse();

        $response = new ResponseSuccess();
        $response->setResult(new ResponseData('', ['modal' => $wrapper->parse()]));

        return $response;
    }

    protected function getPathsFromOptions(array $options)
    {
        $files = [];
        foreach ($options as $option) {
            if (is_array($option) && null !== ($path = System::getContainer()->get('huh.utils.file')->getPathFromUuid($option['uuid']))) {
                $files[$path] = $option['title'];
            }
//            elseif(null !== ($path = System::getContainer()->get('huh.utils.file')->getPathFromUuid($option))) {
//                $files[$path] = '';
//            }
        }

        return $files;
    }
}
