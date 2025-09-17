<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\ContaoManager;

use Codefog\TagsBundle\CodefogTagsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use HeimrichHannot\MediaLibraryBundle\HeimrichHannotMediaLibraryBundle;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    public function getBundles(ParserInterface $parser):array
    {
        $loadAfter = [
            ContaoCoreBundle::class,
            CodefogTagsBundle::class,
        ];

        if (class_exists('HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle')) {
            $loadAfter[] = \HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle::class;
        }

        return [
            BundleConfig::create(HeimrichHannotMediaLibraryBundle::class)->setLoadAfter($loadAfter),
        ];
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        $extensionConfigs = ContainerUtil::mergeConfigFile(
            'huh_list',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/heimrichhannot/contao-media-library-bundle/src/Resources/config/config_list.yml'
        );

        return ContainerUtil::mergeConfigFile(
            'huh_reader',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/heimrichhannot/contao-media-library-bundle/src/Resources/config/config_reader.yml'
        );
    }
}
