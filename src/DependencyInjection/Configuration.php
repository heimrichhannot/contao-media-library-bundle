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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('huh_media_library');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->booleanNode('sanitize_download_filenames')
                    ->info('If true, the filenames of the generated product downloads will be sanitized.')
                    ->defaultFalse()
                ->end()
                ?->scalarNode('file_upload_path')
                    ->info('The path where the uploaded files will be stored.')
                    ->defaultValue('%kernel.project_dir%/files/media-library/##author:id##/##title##')
                ->end()
            ?->end();

        return $treeBuilder;
    }
}
