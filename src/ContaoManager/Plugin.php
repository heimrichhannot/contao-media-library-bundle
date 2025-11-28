<?php

namespace HeimrichHannot\MediaLibraryBundle\ContaoManager;

use Codefog\TagsBundle\CodefogTagsBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use HeimrichHannot\FileCreditsBundle\HeimrichHannotFileCreditsBundle;
use HeimrichHannot\FlareBundle\HeimrichHannotFlareBundle;
use HeimrichHannot\MediaLibraryBundle\HeimrichHannotMediaLibraryBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser):array
    {
        $loadAfter = [
            ContaoCoreBundle::class,
        ];

        if (\class_exists(CodefogTagsBundle::class)) {
            $loadAfter[] = CodefogTagsBundle::class;
        }

        if (\class_exists(HeimrichHannotFileCreditsBundle::class)) {
            $loadAfter[] = HeimrichHannotFileCreditsBundle::class;
        }

        if (\class_exists(HeimrichHannotFlareBundle::class)) {
            $loadAfter[] = HeimrichHannotFlareBundle::class;
        }

        return [
            BundleConfig::create(HeimrichHannotMediaLibraryBundle::class)->setLoadAfter($loadAfter),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): ?RouteCollection
    {
        return $resolver->resolve($routes = '@HeimrichHannotMediaLibraryBundle/config/routes.yaml')->load($routes);
    }
}
