<?php

namespace HeimrichHannot\MediaLibraryBundle\Manager;

use Contao\CoreBundle\Image\ImageFactoryInterface;
use Contao\CoreBundle\Image\Studio\ImageResult;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\FilesModel;
use Contao\Image\PictureConfiguration;
use Contao\ImageSizeModel;
use HeimrichHannot\MediaLibraryBundle\Dto\ImageSizeDownloadDto;

readonly class DownloadsManager
{
    public function __construct(
        private ImageFactoryInterface $imageFactory,
        private Studio $studio,
    ) {}

    protected function tryCreateFigureImage(
        string                                     $fileUuid,
        PictureConfiguration|array|int|string|null $imageSize = null
    ): ?ImageResult {
        try
        {
            $figureBuilder = $this->studio
                ->createFigureBuilder()
                ->fromUuid($fileUuid);

            if ($imageSize)
            {
                $figureBuilder->setSize($imageSize);
            }

            $image = $figureBuilder->build()->getImage();

            // validate image can be created
            $this->imageFactory->create($image->getFilePath(absolute: true));

            return $image;
        }
        catch (\Throwable) {}

        return null;
    }

    protected function getFilesModelByUuid(
        FilesModel|string|null $file,
    ): ?FilesModel {
        if ($file instanceof FilesModel) {
            return $file;
        }

        if (!$file || !\is_string($file)) {
            return null;
        }

        return FilesModel::findByUuid($file) ?: null;
    }

    public function createImageDownload(FilesModel $file): ?ImageSizeDownloadDto
    {
        if (!$image = $this->tryCreateFigureImage($file->uuid)) {
            return null;
        }

        $dimensions = $image->getOriginalDimensions();
        $imgPath = $image->getFilePath(true);
        $fileSize = ($imgPath && \file_exists($imgPath)) ? \filesize($imgPath) : 0;
        $fileSize = $fileSize ?: null;

        return ImageSizeDownloadDto::create()
            ->setLabel('Original')
            ->setUrl($image->getImageSrc())
            ->setWidth($dimensions->getSize()->getWidth())
            ->setHeight($dimensions->getSize()->getHeight())
            ->setFilesModel($file)
            ->setFilesize($fileSize)
        ;
    }

    public function createImageSizeDownload(FilesModel $file, $imageSize): ?ImageSizeDownloadDto
    {
        /** @var $imageSizeModel ImageSizeModel */
        if (!$imageSizeModel = ImageSizeModel::findByPk($imageSize)) {
            return null;
        }

        if (!$image = $this->tryCreateFigureImage($file->uuid, $imageSize)) {
            return null;
        }

        $filePath = $image->getImageSrc(true);
        $fileSize = ($filePath && \file_exists($filePath)) ? \filesize($filePath) : 0;
        $fileSize = $fileSize ?: null;

        return ImageSizeDownloadDto::create()
            ->setLabel($imageSizeModel->name ?: \sprintf('[ID %d]', $imageSizeModel->id))
            ->setUrl($image->getImageSrc())
            ->setFilesModel($file)
            ->setImageSizeModel($imageSizeModel)
            ->setFilesize($fileSize)
        ;
    }

    /**
     * @param FilesModel|string|null $file
     * @param ?array $imageSizes
     * @param array{
     *      withOriginal?: bool
     *  } $options
     * @return ImageSizeDownloadDto[]
     */
    public function createImageSizeDownloads(
        FilesModel|string|null $file,
        array|null             $imageSizes,
        array                  $options = []
    ): array {
        if (!$file = $this->getFilesModelByUuid($file)) {
            return [];
        }

        if (!$fileUuid = $file->uuid) {
            return [];
        }

        [
            'with_original' => $withOriginal,
        ] = $options + [
            'with_original' => true,
        ];

        /** @var ImageSizeDownloadDto[] $downloads */
        $downloads = [];

        if ($withOriginal && $original = $this->createImageDownload($file))
            // Add original image
        {
            $downloads[] = $original;
        }

        if (!$imageSizes)
        {
            return $downloads;
        }

        foreach ($imageSizes as $imageSize)
            // Add image sizes
        {
            if ($download = $this->createImageSizeDownload($file, $imageSize))
            {
                $downloads[] = $download;
            }
        }

        return $downloads;
    }
}