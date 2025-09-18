<?php

namespace HeimrichHannot\MediaLibraryBundle\Model;

use Contao\Model;
use HeimrichHannot\MediaLibraryBundle\DataContainer\DownloadContainer;

class DownloadModel extends Model
{
    protected static $strTable = DownloadContainer::TABLE;
}