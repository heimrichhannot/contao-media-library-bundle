<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Tests;

use HeimrichHannot\MediaLibraryBundle\HeimrichHannotContaoMediaLibraryBundle;
use PHPUnit\Framework\TestCase;

class HeimrichHannotMediaLibraryBundleTest extends TestCase
{
    /**
     * test instantiation.
     */
    public function testCanBeInstantiated()
    {
        $bundle = new HeimrichHannotContaoMediaLibraryBundle();

        $this->assertInstanceOf(HeimrichHannotContaoMediaLibraryBundle::class, $bundle);
    }
}
