<?php

namespace HeimrichHannot\MediaLibraryBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use HeimrichHannot\MediaLibraryBundle\HeimrichHannotMediaLibraryBundle;

class EncoreExtension implements EncoreExtensionInterface
{

    public function getBundle(): string
    {
        return HeimrichHannotMediaLibraryBundle::class;
    }

    public function getEntries(): array
    {
        return [
            // EncoreEntry::create('contao-media-library-bundle', 'src/Resources/assets/js/contao-media-library-bundle.js')
            //     ->setRequiresCss(true),
        ];
    }
}