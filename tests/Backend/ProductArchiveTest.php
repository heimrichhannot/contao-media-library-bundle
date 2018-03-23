<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Test\Backend;

use Contao\CoreBundle\Image\ImageSizes;
use Contao\DataContainer;
use Contao\System;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\MediaLibraryBundle\Backend\ProductArchive;
use HeimrichHannot\UtilsBundle\Dca\DcaUtil;

class ProductArchiveTest extends ContaoTestCase
{
    private $framework;

    public function setUp()
    {
        parent::setUp();

        $this->framework = $this->mockContaoFramework();
    }

    public function testGetPaletteFields()
    {
        $container = $this->mockContainer();

        $dcaUtil = $this->createMock(DcaUtil::class);
        $dcaUtil->method('getFields')->willReturn(['field1', 'field2', 'field3']);

        $container->set('huh.utils.dca', $dcaUtil);

        System::setContainer($container);

        $productArchive = new ProductArchive($this->framework);

        $fields = $productArchive->getPaletteFields();

        $this->assertNotEmpty($fields);
        $this->assertCount(3, $fields);
        $this->assertSame(['field1', 'field2', 'field3'], $fields);
    }

//    public function testGetImageSizes()
//    {
//        $container = $this->mockContainer();
//
//        $imageSizes = $this->createMock(ImageSizes::class);
//        $imageSizes->method('getOptionsForUser')->willReturn(['imageSizes' => ['option1','option2','option3']]);
//
//        $container->set('contao.image.image_sizes',$imageSizes);
//
//        System::setContainer($container);
//
//        $productArchive = new ProductArchive($this->framework);
//
//        $options = $productArchive->getImageSizes();
//
//        $this->assertNotEmpty($options);
//        $this->assertCount(3,$options);
//        $this->assertEquals(['option1','option2','option3'],$options);
//    }

    public function testGetFieldsForTag()
    {
        $dc = $this->mockClassWithProperties(DataContainer::class, ['activeRecord' => new \stdClass()]);
        $dc->activeRecord->palette = serialize(['field1', 'field2', 'field3']);

        $productArchive = new ProductArchive($this->framework);

        $paletteFields = $productArchive->getFieldsForTag($dc);

        $this->assertNotEmpty($paletteFields);
        $this->assertCount(3, $paletteFields);
        $this->assertSame(['field1', 'field2', 'field3'], $paletteFields);
    }
}
