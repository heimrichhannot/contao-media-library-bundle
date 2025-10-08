<?php

namespace HeimrichHannot\MediaLibraryBundle\Twig\Runtime;

use Contao\Image\ResizeConfiguration;
use Contao\ImageSizeModel;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\Manager\DownloadsManager;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Twig\Extension\RuntimeExtensionInterface;

readonly class MediaLibraryRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private ArchiveTypeCollection $archiveTypes,
        private DownloadsManager      $downloads,
    ) {}

    protected function getImageSizeSupportingArchive(ItemModel $itemModel): ?ArchiveModel
    {
        if (!$archive = $itemModel->getArchive()) {
            return null;
        }

        if (!$archiveType = $this->archiveTypes->get($archive->type)) {
            return null;
        }

        if (!$archiveType->supportsImageSizeDownloads($archive)) {
            return null;
        }

        return $archive;
    }

    public function getImageSizeDownloads(ItemModel $itemModel, array $options = []): array
    {
        if (!$archive = $this->getImageSizeSupportingArchive($itemModel)) {
            return [];
        }

        return $this->downloads->createImageSizeDownloads($itemModel->file, $archive->getImageSizes(), $options);
    }

    public function getVariantImageSizeDownloads(ItemModel $itemModel, array $options = []): array
    {
        if (!$archive = $this->getImageSizeSupportingArchive($itemModel)) {
            return [];
        }

        if (!$imageSizes = $archive->getImageSizes()) {
            return [];
        }

        if (!$variants = $itemModel->getVariants()) {
            return [];
        }

        $variantDownloads = [];

        foreach ($variants as $variant)
        {
            if ($downloads = $this->downloads->createImageSizeDownloads($variant, $imageSizes, $options))
            {
                $variantDownloads[] = $downloads;
            }
        }

        return $variantDownloads;
    }

    public function getImageSizeInfo(?ImageSizeModel $model): string
    {
        if (!$model instanceof ImageSizeModel) {
            return '';
        }

        $w = $model->width ?: null;
        $h = $model->height ?: null;

        $proportional = \defined(ResizeConfiguration::class . '::MODE_PROPORTIONAL')
            ? ResizeConfiguration::MODE_PROPORTIONAL
            : 'proportional';

        // calculate aspect ratio
        $a = null;
        $b = null;
        if ($w && $h)
        {
            $gcd = static function ($a, $b) use (&$gcd) {
                return $b ? $gcd($b, $a % $b) : $a;
            };
            $divisor = $gcd($w, $h);
            $a = $w / $divisor;
            $b = $h / $divisor;
        }

        $return = match ($model->resizeMode)
        {
            ResizeConfiguration::MODE_BOX => 'Passend',
            ResizeConfiguration::MODE_CROP => 'Zuschnitt'
                . (($a && $b) ? \sprintf(', Seitenverhältnis %d⁠:⁠%d', $a, $b) : ''),
            $proportional => 'Proportional',
            default => $model->resizeMode,
        };

        if ($w || $h) {
            $return .= \sprintf(', max. %d⁠ × %d px', $w ?: '∞', $h ?: '∞');
        }

        return $return;
    }

    public function getFilesizeHumanReadable(
        string|int|null $filesize,
        ?int            $decimals = null,
        ?string         $decimalSeparator = null,
        ?string         $thousandsSeparator = null,
    ): string {
        if ($filesize === null || $filesize === '' || $filesize === 0) {
            return '-';
        }

        if (!\is_numeric($filesize)) {
            return '-';
        }

        $decimals ??= 2;
        $decimalSeparator ??= ',';
        $thousandsSeparator ??= '.';

        $decimals = \max(0, $decimals);

        $intFilesize = (int) $filesize;

        if ($intFilesize < 1024) {
            return \sprintf("%d B", $intFilesize);
        }

        $format = static fn (float $n) => \number_format($n, $decimals, $decimalSeparator, $thousandsSeparator);

        if ($filesize < 1048576) {
            return \sprintf("%s kB", $format($intFilesize / 1024));
        }

        if ($filesize < 1073741824) {
            return \sprintf("%s MB", $format($filesize / 1048576));
        }

        return \sprintf("%s GB", $format($filesize / 1073741824));
    }
}