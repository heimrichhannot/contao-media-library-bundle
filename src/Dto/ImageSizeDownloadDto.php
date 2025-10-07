<?php

namespace HeimrichHannot\MediaLibraryBundle\Dto;

use Contao\FilesModel;
use Contao\ImageSizeModel;

class ImageSizeDownloadDto
{
    private string $label;
    private string $url;
    private int $width;
    private int $height;
    private ?int $filesize = null;
    private FilesModel $filesModel;
    private ?ImageSizeModel $imageSizeModel = null;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    public function setFilesize(?int $filesize): self
    {
        $this->filesize = $filesize;

        return $this;
    }

    public function getFilesModel(): FilesModel
    {
        return $this->filesModel;
    }

    public function setFilesModel(FilesModel $filesModel): self
    {
        $this->filesModel = $filesModel;

        return $this;
    }

    public function getImageSizeModel(): ?ImageSizeModel
    {
        return $this->imageSizeModel;
    }

    public function setImageSizeModel(ImageSizeModel $imageSizeModel): self
    {
        $this->imageSizeModel = $imageSizeModel;

        return $this;
    }

    public static function create(): self
    {
        return new self();
    }
}