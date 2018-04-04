<?php

/*
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Test\Registry;

use Contao\Model;
use Contao\TestCase\ContaoTestCase;
use HeimrichHannot\MediaLibraryBundle\Model\DownloadModel;
use HeimrichHannot\MediaLibraryBundle\Registry\DownloadRegistry;
use HeimrichHannot\UtilsBundle\Model\ModelUtil;

class DownloadRegistryTest extends ContaoTestCase
{
    protected $framework;

    protected $modelUtil;

    public function setUp()
    {
        parent::setUp();

        $downloadModel = $this->mockClassWithProperties(DownloadModel::class, ['id' => 1, 'author' => 0]);

        $downloadAdapter = $this->mockAdapter(['findBy']);
        $downloadAdapter->method('findBy')->willReturn($downloadModel);

        $modelAdapter = $this->mockAdapter(['getClassFromTable']);
        $modelAdapter->method('getClassFromTable')->wilLReturn(DownloadModel::class);

        $this->framework = $this->mockContaoFramework([DownloadModel::class => $downloadAdapter, Model::class => $modelAdapter]);
        $this->modelUtil = new ModelUtil($this->framework);
    }

    public function testFindBy()
    {
        $downloadRegistry = new DownloadRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findBy(['id'], [1]);

        $this->assertInstanceOf(DownloadModel::class, $result);
    }

    public function testFindOneBy()
    {
        $downloadRegistry = new DownloadRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findOneBy(['id'], [1]);

        $this->assertInstanceOf(DownloadModel::class, $result);
    }

    public function textFindByPk()
    {
        $downloadRegistry = new DownloadRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findByPk([1]);

        $this->assertInstanceOf(DownloadModel::class, $result);
    }

    public function testFindGeneratedImages()
    {
        $downloadRegistry = new DownloadRegistry($this->framework, $this->modelUtil);

        $result = $downloadRegistry->findGeneratedImages(1);

        $this->assertInstanceOf(DownloadModel::class, $result);
    }
}
