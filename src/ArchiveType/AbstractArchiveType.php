<?php

namespace HeimrichHannot\MediaLibraryBundle\ArchiveType;

abstract class AbstractArchiveType
{
    abstract public static function getAlias(): string;
}