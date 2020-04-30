<?php

/*
 * Copyright (c) 2020 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MediaLibraryBundle\Event;

use Contao\Image\Image;
use Contao\Model;
use Symfony\Component\EventDispatcher\Event;

class BeforeCreateImageDownloadEvent extends Event
{
    public const NAME = 'huh.media_library.before_create_image_download';
    /**
     * @var Image|null
     */
    private $resizeImage;
    /**
     * @var Model|null
     */
    private $originalFile;
    /**
     * @var string
     */
    private $targetFilename;
    private $size;

    public function __construct(?Image $resizeImage, ?Model $originalFile, string $targetFilename, $size)
    {
        $this->resizeImage = $resizeImage;
        $this->originalFile = $originalFile;
        $this->targetFilename = $targetFilename;
        $this->size = $size;
    }

    public function getResizeImage(): ?Image
    {
        return $this->resizeImage;
    }

    public function getOriginalFile(): ?Model
    {
        return $this->originalFile;
    }

    public function getTargetFilename(): string
    {
        return $this->targetFilename;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }
}
