<?php

declare(strict_types=1);

namespace HeimrichHannot\MediaLibraryBundle\DependencyInjection\Compiler;

use HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle;
use HeimrichHannot\MediaLibraryBundle\EventListener\Integration\HuhFilecreditsListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class HuhFilecreditsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (\class_exists(HeimrichHannotFileCreditsBundle::class)) {
            return;
        }

        if ($container->hasDefinition(HuhFilecreditsListener::class)) {
            $container->removeDefinition(HuhFilecreditsListener::class);
        }
    }
}