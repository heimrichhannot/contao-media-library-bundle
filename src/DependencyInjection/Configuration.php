<?php

/*
 * Copyright (c) 2022 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('huh_media_library');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('sanitize_download_filenames')
                    ->info('If true, the filenames of the generated product downloads will be sanitized.')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
