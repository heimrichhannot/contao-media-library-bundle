<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Test\Registry;

use Contao\Model;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\MediaLibraryBundle\Model\ProductModel;
use HeimrichHannot\MediaLibraryBundle\Registry\ProductRegistry;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ProductRegistryTest extends ContaoTestCase
{
    protected $framework;

    protected $modelUtil;

    public function setUp()
    {
        parent::setUp();

        $downloadModel = $this->mockClassWithProperties(ProductModel::class, ['id' => 1, 'author' => 0]);

        $downloadAdapter = $this->mockAdapter(['findBy']);
        $downloadAdapter->method('findBy')->willReturn($downloadModel);

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->wilLReturn(ProductModel::class);

        $this->framework = $this->mockContaoFramework([ProductModel::class => $downloadAdapter, Model::class => $modelAdapter]);
        $this->modelUtil = new ModelUtil($this->framework);
    }

    public function testFindBy()
    {
        $downloadRegistry = new ProductRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findBy(['id'], [1]);

        $this->assertInstanceOf(ProductModel::class, $result);
    }

    public function testFindOneBy()
    {
        $downloadRegistry = new ProductRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findOneBy(['id'], [1]);

        $this->assertInstanceOf(ProductModel::class, $result);
    }

    public function textFindByPk()
    {
        $downloadRegistry = new ProductRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findByPk([1]);

        $this->assertInstanceOf(ProductModel::class, $result);
    }
}
