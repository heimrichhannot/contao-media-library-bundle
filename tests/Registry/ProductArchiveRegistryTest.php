<?php

/*
 * Copyright (c) 2019 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Test\Registry;

use Contao\Model;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\MediaLibraryBundle\Model\ProductArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Registry\ProductArchiveRegistry;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class ProductArchiveRegistryTest extends ContaoTestCase
{
    protected $framework;

    protected $modelUtil;

    public function setUp()
    {
        parent::setUp();

        $downloadModel = $this->mockClassWithProperties(ProductArchiveModel::class, ['id' => 1, 'author' => 0]);

        $downloadAdapter = $this->mockAdapter(['findBy']);
        $downloadAdapter->method('findBy')->willReturn($downloadModel);

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->wilLReturn(ProductArchiveModel::class);

        $this->framework = $this->mockContaoFramework([ProductArchiveModel::class => $downloadAdapter, Model::class => $modelAdapter]);
        $this->modelUtil = new ModelUtil($this->framework);
    }

    public function testFindBy()
    {
        $downloadRegistry = new ProductArchiveRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findBy(['id'], [1]);

        $this->assertInstanceOf(ProductArchiveModel::class, $result);
    }

    public function testFindOneBy()
    {
        $downloadRegistry = new ProductArchiveRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findOneBy(['id'], [1]);

        $this->assertInstanceOf(ProductArchiveModel::class, $result);
    }

    public function textFindByPk()
    {
        $downloadRegistry = new ProductArchiveRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findByPk([1]);

        $this->assertInstanceOf(ProductArchiveModel::class, $result);
    }
}
