<?php

namespace HeimrichHannot\MediaLibraryBundle\DependencyInjection;

use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
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

        if ($this->isCodefogTagsBundleInstalled())
        {
            $loader->load('integrations/codefog_tags.services.yaml');
        }

        if ($this->isHuhFilecreditsBundleInstalled())
        {
            $loader->load('integrations/huh_filecredits.services.yaml');
        }

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
        if ($this->isCodefogTagsBundleInstalled())
        {
            $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__) . '/../config'));
            $loader->load('integrations/codefog_tags.config.yaml');
        }
    }

    private function isCodefogTagsBundleInstalled(): bool
    {
        return InstalledVersions::satisfies(new VersionParser(), 'codefog/tags-bundle', '^3.0');
    }

    private function isHuhFilecreditsBundleInstalled(): bool
    {
        return InstalledVersions::isInstalled('heimrichhannot/contao-filecredits-bundle');
    }
}