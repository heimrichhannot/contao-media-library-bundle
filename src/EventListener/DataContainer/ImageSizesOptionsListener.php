<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener\DataContainer;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Image\ImageSizes;
use Contao\ImageSizeModel;
use Contao\ThemeModel;
use HeimrichHannot\MediaLibraryBundle\DataContainer\ArchiveContainer;

#[AsCallback(ArchiveContainer::TABLE, 'fields.imageSizes.options')]
readonly class ImageSizesOptionsListener
{
    public function __construct(
        private ImageSizes $imageSizes,
    ) {}

    public function __invoke(): array
    {
        $user = BackendUser::getInstance();
        $imageSizes = $this->imageSizes->getOptionsForUser($user);

        $parents = [];
        $options = [];

        foreach ($imageSizes as $key => $size)
        {
            if (\in_array($key, ['image_sizes', 'relative', 'exact'])) {
                continue;
            }

            foreach ($size as $id => $label)
            {
                if (($model = ImageSizeModel::findByPk($id)) && $model->pid)
                {
                    $parents[$model->id] = $model->pid;
                }

                $options[$id] = "$label [ID $id]";
            }
        }

        if (\count(\array_unique($parents)) > 1)
            // Only show the theme name if there are image sizes from different themes
        {
            foreach ($options as $id => $label)
            {
                if (!$pid = $parents[$id] ?? null) {
                    continue;
                }

                if (!$theme = ThemeModel::findByPk($pid)) {
                    continue;
                }

                $options[$id] = "[{$theme->name}] $label";
            }
        }

        \asort($options);

        return $options;
    }
}