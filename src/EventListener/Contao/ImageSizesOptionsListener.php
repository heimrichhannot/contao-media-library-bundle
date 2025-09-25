<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\Contao;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Image\ImageSizes;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ArchiveContainer;
use HeimrichHannot\MediaLibraryBundle\DataContainer\DownloadNewContainer;

#[AsCallback(ArchiveContainer::TABLE, 'fields.imageSizes.options')]
#[AsCallback(DownloadNewContainer::TABLE, 'fields.imageSizes.options')]
readonly class ImageSizesOptionsListener
{
    public function __construct(
        private ImageSizes $imageSizes,
    ) {}

    public function __invoke(): array
    {
        $user = BackendUser::getInstance();
        $imageSizes = $this->imageSizes->getOptionsForUser($user);

        $options = [];

        foreach ($imageSizes as $key => $size)
        {
            if (\in_array($key, ['image_sizes', 'relative', 'exact'])) {
                continue;
            }

            foreach ($size as $id => $label) {
                $options[$id] = "$label [ID $id]";
            }
        }

        return $options;
    }
}