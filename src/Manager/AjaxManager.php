<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Manager;

use HeimrichHannot\Ajax\Response\ResponseError;

class AjaxManager
{
    const MEDIA_LIBRARY_XHR_GROUP = 'media-library';

    const MEDIA_LIBRARY_DOWNLOAD_SHOW_OPTIONS = 'showOptionsModal';

    const MEDIA_LIBRARY_ARGUMENTS_OPTIONS = 'options';

    public function showOptionsModal($options)
    {
        if (!$options) {
            return new ResponseError();
        }
    }
}
