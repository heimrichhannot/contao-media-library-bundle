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
            new TwigFunction('ml_image_size_variant_downloads', [MediaLibraryRuntime::class, 'getVariantImageSizeDownloads']),
            new TwigFunction('ml_filesize', [MediaLibraryRuntime::class, 'getFilesize']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('ml_image_size_info', [MediaLibraryRuntime::class, 'getImageSizeInfo']),
            new TwigFilter('ml_filesize_human_readable', [MediaLibraryRuntime::class, 'getFilesizeHumanReadable']),
        ];
    }
}