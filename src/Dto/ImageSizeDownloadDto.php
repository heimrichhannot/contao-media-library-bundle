<?php

namespace HeimrichHannot\MediaLibraryBundle\Dto;

use Contao\ImageSizeModel;

class ImageSizeDownloadDto extends FileDownloadDto
{
    protected int $width;
    protected int $height;
    protected ?ImageSizeModel $imageSizeModel = null;

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

    public function getImageSizeModel(): ?ImageSizeModel
    {
        return $this->imageSizeModel;
    }

    public function setImageSizeModel(ImageSizeModel $imageSizeModel): self
    {
        $this->imageSizeModel = $imageSizeModel;

        return $this;
    }
}