<?php

namespace HeimrichHannot\MediaLibraryBundle\EventListener;

use Contao\CoreBundle\Filesystem\FilesystemItem;
use Contao\CoreBundle\Filesystem\VirtualFilesystemInterface;
use Contao\StringUtil;
use HeimrichHannot\FlareBundle\Event\ReaderRenderEvent;
use HeimrichHannot\MediaLibraryBundle\Flare\ListType\MediaLibraryFilesListDriverInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Uid\Uuid;

#[AsEventListener]
readonly class FlareReaderListener
{
    public function __construct(
        private VirtualFilesystemInterface $filesStorage,
    ) {}

    public function __invoke(ReaderRenderEvent $event): void
    {
        $list = $event->list;

        if (!$list->driver instanceof MediaLibraryFilesListDriverInterface) {
            return;
        }

        $event->set('file', $this->getFile($event));
        $event->set('additionalFiles', $this->getAdditionalFiles($event));
    }

    public function getFile(ReaderRenderEvent $event): ?FilesystemItem
    {
        if (!$uuidBin = $event->displayModel->file) {
            return null;
        }

        try {
            $uuid = Uuid::fromBinary($uuidBin);
        } catch (\InvalidArgumentException) {
            return null;
        }

        if (!$file = $this->filesStorage->get($uuid)) {
            return null;
        }

        return $file;
    }

    public function getAdditionalFiles(ReaderRenderEvent $event): ?array
    {
        $model = $event->displayModel;

        if (!$model->addAdditionalFiles) {
            return null;
        }

        if (!$fileUuids = StringUtil::deserialize($model->additionalFiles, true)) {
            return null;
        }

        $files = [];

        foreach ($fileUuids as $fileUuidBin) {
            try {
                $uuid = Uuid::fromBinary($fileUuidBin);
            } catch (\InvalidArgumentException) {
                continue;
            }

            if ($file = $this->filesStorage->get($uuid)) {
                $files[] = $file;
            }
        }

        return $files;
    }
}
