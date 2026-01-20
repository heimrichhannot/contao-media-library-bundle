<?php

namespace HeimrichHannot\MediaLibraryBundle\Twig\Runtime;

use Contao\FilesModel;
use Contao\Image\ResizeConfiguration;
use Contao\ImageSizeModel;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\Dto\FileDownloadDto;
use HeimrichHannot\MediaLibraryBundle\Manager\DownloadsManager;
use HeimrichHannot\MediaLibraryBundle\Model\ArchiveModel;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\RuntimeExtensionInterface;

readonly class MediaLibraryRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private ArchiveTypeCollection $archiveTypes,
        private DownloadsManager      $downloads,
        private ParameterBagInterface $parameters,
    ) {}

    public function getFileDownload(FilesModel|string|null $file, array $options = []): ?FileDownloadDto
    {
        if (!$file instanceof FilesModel) {
            $file = FilesModel::findByUuid($file);
        }

        if (!$file instanceof FilesModel) {
            return null;
        }

        return $this->downloads->createDownload($file);
    }

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

        if (!$variants = $itemModel->getVariants()) {
            return [];
        }

        $imageSizes = $archive->getImageSizes();

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

    public function getFilesize(FilesModel|null $file): ?int
    {
        if (!$file instanceof FilesModel || $file->type !== 'file' || !$file->path)
        {
            return null;
        }

        $projectDir = \rtrim($this->parameters->get('kernel.project_dir'), '/') . '/';
        $filepath = \str_starts_with($file->path, $projectDir)
            ? $file->path
            : ($projectDir . \ltrim($file->path, '/'));

        if (!\file_exists($filepath)) {
            return null;
        }

        if (false === ($filesize = \filesize($filepath))) {
            return null;
        }

        return $filesize;
    }

    public function getFilesizeHumanReadable(
        string|int|null $filesize,
        ?int            $decimals = null,
        ?string         $decimalSeparator = null,
        ?string         $thousandsSeparator = null,
    ): string {
        if ($filesize === null || $filesize === '') {
            return '-';
        }

        if (!\is_numeric($filesize)) {
            return '-';
        }

        $intFilesize = (int) $filesize;

        if ($intFilesize <= 0) {
            return '0 B';
        }

        if ($intFilesize < 1024) {
            return \sprintf("%d B", $intFilesize);
        }

        $decimals = \max(0, $decimals ?? 2);
        $decimalSeparator ??= ',';
        $thousandsSeparator ??= '.';

        $format = static fn (float $n) => \number_format($n, $decimals, $decimalSeparator, $thousandsSeparator);

        if ($intFilesize < 1048576) {
            return \sprintf("%s kB", $format($intFilesize / 1024));
        }

        if ($intFilesize < 1073741824) {
            return \sprintf("%s MB", $format($intFilesize / 1048576));
        }

        return \sprintf("%s GB", $format($intFilesize / 1073741824));
    }
}