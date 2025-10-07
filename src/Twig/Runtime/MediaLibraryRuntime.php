<?php

namespace HeimrichHannot\MediaLibraryBundle\Twig\Runtime;

use Contao\CoreBundle\Image\Studio\Studio;
use Contao\Image\ResizeConfiguration;
use Contao\ImageSizeModel;
use HeimrichHannot\MediaLibraryBundle\Collection\ArchiveTypeCollection;
use HeimrichHannot\MediaLibraryBundle\Dto\ImageSizeDownloadDto;
use HeimrichHannot\MediaLibraryBundle\Model\ItemModel;
use Twig\Extension\RuntimeExtensionInterface;

readonly class MediaLibraryRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private ArchiveTypeCollection $archiveTypes,
        private Studio                $studio,
    ) {}

    /**
     * @param ItemModel $itemModel
     * @param array{
     *     withOriginal?: bool
     * } $options
     * @return ImageSizeDownloadDto[]
     */
    public function getImageSizeDownloads(ItemModel $itemModel, array $options = []): array
    {
        if (!$archive = $itemModel->getArchive()) {
            return [];
        }

        if (!$archiveType = $this->archiveTypes->get($archive->type)) {
            return [];
        }

        if (!$archiveType->supportsImageSizeDownloads($archive)) {
            return [];
        }

        if (!$filesModel = $itemModel->getFile()) {
            return [];
        }

        [
            'with_original' => $withOriginal,
        ] = $options + [
            'with_original' => true,
        ];

        /** @var ImageSizeDownloadDto[] $downloads */
        $downloads = [];

        $figureBuilder = $this->studio->createFigureBuilder();

        try {
            $figureBuilder
                ->fromUuid($filesModel->uuid)
                ->build();
        }
        catch (\Exception)
        {
            return [];
        }

        if ($withOriginal)
        {
            $figure = $figureBuilder
                ->fromUuid($filesModel->uuid)
                ->build();

            $dimensions = $figure->getImage()->getOriginalDimensions();

            $original = ImageSizeDownloadDto::create()
                ->setLabel('Original')
                ->setUrl($figure->getImage()->getImageSrc())
                ->setWidth($dimensions->getSize()->getWidth())
                ->setHeight($dimensions->getSize()->getHeight())
                ->setFilesModel($filesModel)
            ;

            $downloads[] = $original;
        }

        if (!$imageSizes = $archive->getImageSizes()) {
            return $downloads;
        }

        foreach ($imageSizes as $imageSize)
        {
            $figure = $figureBuilder
                ->fromUuid($filesModel->uuid)
                ->setSize($imageSize)
                ->build();

            if (!$imageSizeModel = ImageSizeModel::findByPk($imageSize)) {
                continue;
            }

            $download = ImageSizeDownloadDto::create()
                ->setLabel($imageSizeModel->name ?: \sprintf('[ID %d]', $imageSizeModel->id))
                ->setUrl($figure->getImage()->getImageSrc())
                ->setFilesModel($filesModel)
                ->setImageSizeModel($imageSizeModel)
            ;

            $downloads[] = $download;
        }

        return $downloads;
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
        $a = $w;
        $b = $h;
        if ($w && $h)
        {
            $gcd = function ($a, $b) use (&$gcd) {
                return $b ? $gcd($b, $a % $b) : $a;
            };
            $divisor = $gcd($w, $h);
            $a = $w / $divisor;
            $b = $h / $divisor;
        }

        $return = match ($model->resizeMode)
        {
            ResizeConfiguration::MODE_BOX => 'Passend',
            ResizeConfiguration::MODE_CROP => \sprintf('Zuschnitt, Seitenverhältnis %d⁠:⁠%d', $a, $b),
            $proportional => 'Proportional',
            default => $model->resizeMode,
        };

        if ($w || $h) {
            $return .= \sprintf(', max. %d⁠ × %d px', $w ?: '∞', $h ?: '∞');
        }

        return $return;
    }
}