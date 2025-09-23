<?php

namespace HeimrichHannot\MediaLibraryBundle\Util;

use Contao\StringUtil;

readonly class Str
{
    /**
     * Merges multiple palettes into one.
     *
     * @param string ...$palettes
     */
    public static function mergePalettes(?string ...$palettes): string
    {
        $palettes = \array_filter($palettes);
        \array_walk($palettes, static fn (string $palette): string => \trim($palette, ";, \n\r\t\v\0"));
        return \implode(';', \array_filter($palettes));
    }
}