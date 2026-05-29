<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle;

use HeimrichHannot\MediaLibraryBundle\DependencyInjection\HeimrichHannotMediaLibraryExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotContaoMediaLibraryBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new HeimrichHannotMediaLibraryExtension();
    }
}
