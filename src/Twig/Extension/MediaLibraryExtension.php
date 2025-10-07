<?php

declare(strict_types=1);

namespace HeimrichHannot\MediaLibraryBundle\Twig\Extension;

use HeimrichHannot\MediaLibraryBundle\Twig\Runtime\MediaLibraryRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class MediaLibraryExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ml_image_size_downloads', [MediaLibraryRuntime::class, 'getImageSizeDownloads']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ml_image_size_info', [MediaLibraryRuntime::class, 'getImageSizeInfo']),
        ];
    }
}
