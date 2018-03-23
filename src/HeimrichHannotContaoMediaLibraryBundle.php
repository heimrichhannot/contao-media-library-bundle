<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle;

use HeimrichHannot\MediaLibraryBundle\DependencyInjection\HeimrichHannotContaoMediaLibraryExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoMediaLibraryBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new HeimrichHannotContaoMediaLibraryExtension();
    }
}
