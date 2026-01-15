<?php

namespace HeimrichHannot\MediaLibraryBundle\Dto;

use Contao\FilesModel;

class FileDownloadDto
{
    protected string $label;
    protected string $url;
    protected ?int $filesize = null;
    protected FilesModel $filesModel;

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getFilesize(): ?int
    {
        return $this->filesize;
    }

    public function setFilesize(?int $filesize): static
    {
        $this->filesize = $filesize;

        return $this;
    }

    public function getFilesModel(): FilesModel
    {
        return $this->filesModel;
    }

    public function setFilesModel(FilesModel $filesModel): static
    {
        $this->filesModel = $filesModel;

        return $this;
    }

    public static function create(): static
    {
        return new static();
    }
}