<?php

namespace HeimrichHannot\MediaLibraryBundle\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\FilesModel;

class DownloadNewContainer
{
    public const TABLE = 'tl_ml_download';

    #[AsCallback(self::TABLE, 'list.sorting.child_record')]
    public function childRecordCallback(array $row): string
    {
        $data = [];

        if (($fileUuid = $row['file'] ?? null)
            && ($filePath = FilesModel::findByUuid($fileUuid)?->path))
        {
            $data[] = $filePath;
        }

        if ($row['isAdditional'] ?? false)
        {
            $data[] = $GLOBALS['TL_LANG']['MSC']['contaoMediaLibraryBundle']['additional'] ?? 'additional';
        }

        return \sprintf(
            '<div class="tl_content_left">%s <span style="color:#b3b3b3; padding-left:3px">[%s]</span></div>',
            $row['title'] ?: $row['id'],
            \implode(', ', $data)
        );
    }
}