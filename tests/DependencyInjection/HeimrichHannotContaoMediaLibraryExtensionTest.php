<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Test\DependencyInjection;

use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\MediaLibraryBundle\DependencyInjection\HeimrichHannotContaoMediaLibraryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class HeimrichHannotContaoMediaLibraryExtensionTest extends ContaoTestCase
{
    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => false]));
        $extension = new HeimrichHannotContaoMediaLibraryExtension();
        $extension->load([], $container);
    }

    /**
     * Tests the object instantiation.
     */
    public function testCanBeInstantiated()
    {
        $extension = new HeimrichHannotContaoMediaLibraryExtension();

        $this->assertInstanceOf(HeimrichHannotContaoMediaLibraryExtension::class, $extension);
    }
}
