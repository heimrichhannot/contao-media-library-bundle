<?php

declare(strict_types=1);

namespace HeimrichHannot\MediaLibraryBundle\DependencyInjection\Compiler;

use Codefog\TagsBundle\CodefogTagsBundle;
use HeimrichHannot\MediaLibraryBundle\EventListener\Integration\CodefogTagsListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class CodefogTagsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (\class_exists(CodefogTagsBundle::class)) {
            return;
        }

        if ($container->hasDefinition(CodefogTagsListener::class)) {
            $container->removeDefinition(CodefogTagsListener::class);
        }
    }
}