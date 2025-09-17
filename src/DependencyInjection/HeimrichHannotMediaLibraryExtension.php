<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class HeimrichHannotMediaLibraryExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container):void
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__) . '/../config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $mlConfig = $this->processConfiguration($configuration, $configs);

        $container->setParameter($this->getAlias(), $mlConfig);
    }

    public function getAlias(): string
    {
        return 'huh_media_library';
    }
    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__) . '/../config'));
        $loader->load('config.yaml');
    }
}