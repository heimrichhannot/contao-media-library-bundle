<?php

/**
 * @package   Heimrich & Hannot Media-Library Bundle
 * @copyright 2025, Heimrich & Hannot GmbH
 * @license   LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle;

use HeimrichHannot\MediaLibraryBundle\DependencyInjection\Compiler\CodefogTagsPass;
use HeimrichHannot\MediaLibraryBundle\DependencyInjection\Compiler\HuhFilecreditsPass;
use HeimrichHannot\MediaLibraryBundle\DependencyInjection\HeimrichHannotMediaLibraryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotMediaLibraryBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    /**
     * {@inheritdoc}
     *
     * @return class-string<HeimrichHannotMediaLibraryExtension>
     */
    public function getContainerExtensionClass(): string
    {
        return HeimrichHannotMediaLibraryExtension::class;
    }

    public function getContainerExtension(): ?ExtensionInterface
    {
        return $this->extension ??= $this->createContainerExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CodefogTagsPass());
        $container->addCompilerPass(new HuhFilecreditsPass());
    }
}