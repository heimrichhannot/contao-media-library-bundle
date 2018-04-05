<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Tests;

use HeimrichHannot\MediaLibraryBundle\DependencyInjection\HeimrichHannotContaoMediaLibraryExtension;
use HeimrichHannot\MediaLibraryBundle\HeimrichHannotContaoMediaLibraryBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotContaoMediaLibraryBundleTest extends TestCase
{
    /**
     * test instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoMediaLibraryBundle();

        $this->assertInstanceOf(HeimrichHannotContaoMediaLibraryBundle::class, $bundle);
    }

    public function getContainerExtension()
    {
        $bundle = new HeimrichHannotContaoMediaLibraryBundle();

        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(HeimrichHannotContaoMediaLibraryExtension::class, $extension);
    }
}
