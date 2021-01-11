<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Config\ConfigPluginInterface;
use Contao\ManagerPlugin\Config\ContainerBuilder;
use Contao\ManagerPlugin\Config\ExtensionPluginInterface;
use HeimrichHannot\AjaxBundle\HeimrichHannotContaoAjaxBundle;
use HeimrichHannot\MediaLibraryBundle\HeimrichHannotContaoMediaLibraryBundle;
use HeimrichHannot\UtilsBundle\Container\ContainerUtil;
use Symfony\Component\Config\Loader\LoaderInterface;

class Plugin implements BundlePluginInterface, ExtensionPluginInterface, ConfigPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        $loadAfter = [
            ContaoCoreBundle::class,
            HeimrichHannotContaoAjaxBundle::class,
            'filecredits',
        ];

        if (class_exists('HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle')) {
            $loadAfter[] = \HeimrichHannot\ListBundle\HeimrichHannotContaoListBundle::class;
        }

        return [
            BundleConfig::create(HeimrichHannotContaoMediaLibraryBundle::class)->setLoadAfter($loadAfter),
        ];
    }

    /**
     * Allows a plugin to load container configuration.
     */
    public function registerContainerConfiguration(LoaderInterface $loader, array $managerConfig)
    {
        $loader->load('@HeimrichHannotContaoMediaLibraryBundle/Resources/config/services.yml');
        $loader->load('@HeimrichHannotContaoMediaLibraryBundle/Resources/config/datacontainers.yml');
        $loader->load('@HeimrichHannotContaoMediaLibraryBundle/Resources/config/listeners.yml');
    }

    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        $extensionConfigs = ContainerUtil::mergeConfigFile(
            'huh_list',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/heimrichhannot/contao-media-library-bundle/src/Resources/config/config_list.yml'
        );

        $extensionConfigs = ContainerUtil::mergeConfigFile(
            'huh_reader',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir').'/vendor/heimrichhannot/contao-media-library-bundle/src/Resources/config/config_reader.yml'
        );

        return ContainerUtil::mergeConfigFile(
            'huh_encore',
            $extensionName,
            $extensionConfigs,
            $container->getParameter('kernel.project_dir')
            .'/vendor/heimrichhannot/contao-media-library-bundle/src/Resources/config/config_encore.yml'
        );
    }
}
