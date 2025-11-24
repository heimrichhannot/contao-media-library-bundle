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
                    ->setDeprecated(
                        'huh/media-library-bundle',
                        '2.0',
                        'The option "sanitize_download_filenames" is deprecated and will be removed in version 3.0.'
                    )
                ->end()
                ?->scalarNode('file_upload_path')
                    ->info('The path where the uploaded files will be stored.')
                    ->defaultValue('files/media-library/##author##/##title##')
                ->end()
            ?->end();

        return $treeBuilder;
    }
}
