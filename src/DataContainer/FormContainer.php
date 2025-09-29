<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;

class FormContainer
{
    public const TABLE = 'tl_form';

    #[AsCallback(self::TABLE, 'fields.ml_archive.options')]
    public function getMediaLibraryArchiveOptions(): array
    {
        if (!$archives = ArchiveModel::findAll()) {
            return [];
        }

        $options = [];

        foreach ($archives as $archive) {
            $options[$archive->id] = $archive->title;
        }

        return $options;
    }
}
