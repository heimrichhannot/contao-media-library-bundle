<?php

namespace HeimrichHannot\MediaLibraryBundle\Manager;

use Contao\CoreBundle\Image\Studio\Figure;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\FilesModel;
use Contao\Image\PictureConfiguration;
use Contao\ImageSizeModel;
use HeimrichHannot\MediaLibraryBundle\Dto\ImageSizeDownloadDto;

readonly class DownloadsManager
{
    public function __construct(
        private Studio $studio,
    ) {}

    protected function tryCreateFigure(
        string                                     $fileUuid,
        PictureConfiguration|array|int|string|null $imageSize = null
    ): ?Figure {
        try
        {
            $figureBuilder = $this->studio
                ->createFigureBuilder()
                ->fromUuid($fileUuid);

            if ($imageSize)
            {
                $figureBuilder->setSize($imageSize);
            }

            return $figureBuilder->build();
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
        if (!$figure = $this->tryCreateFigure($file->uuid)) {
            return null;
        }

        $dimensions = $figure->getImage()->getOriginalDimensions();
        $imgPath = $figure->getImage()->getFilePath(true);
        $fileSize = ($imgPath && \file_exists($imgPath)) ? \filesize($imgPath) : 0;
        $fileSize = $fileSize ?: null;

        return ImageSizeDownloadDto::create()
            ->setLabel('Original')
            ->setUrl($figure->getImage()->getImageSrc())
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

        if (!$figure = $this->tryCreateFigure($file->uuid, $imageSize)) {
            return null;
        }

        $filePath = $figure->getImage()->getImageSrc(true);
        $fileSize = ($filePath && \file_exists($filePath)) ? \filesize($filePath) : 0;
        $fileSize = $fileSize ?: null;

        return ImageSizeDownloadDto::create()
            ->setLabel($imageSizeModel->name ?: \sprintf('[ID %d]', $imageSizeModel->id))
            ->setUrl($figure->getImage()->getImageSrc())
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