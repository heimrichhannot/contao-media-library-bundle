<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Contao;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use HeimrichHannot\FileCreditsBundle\DataContainer\FileCreditContainer;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * @Hook("loadDataContainer")
 */
class LoadDataContainerListener implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(string $table): void
    {
        if ('tl_ml_product' === $table) {
            if (class_exists(FileCreditContainer::class) && $this->container->has(FileCreditContainer::class)) {
                $this->container->get(FileCreditContainer::class)->addCopyrightFieldToDca(
                    'tl_ml_product', 'copyright', 'file'
                );
                $GLOBALS['TL_DCA']['tl_ml_product']['fields']['copyright']['eval']['tl_class'] = 'clr';
            }
        }
    }

    public static function getSubscribedServices()
    {
        $services = [];

        if (class_exists(FileCreditContainer::class)) {
            $services[] = '?'.FileCreditContainer::class;
        }

        return $services;
    }
}
