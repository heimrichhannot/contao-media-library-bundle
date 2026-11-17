<?php

namespace HeimrichHannot\MediaLibraryBundle\Util;

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

    /**
     * Returns the names of all fields in a palette string, legends excluded.
     *
     * Fields are separated by "," and ";", legends are wrapped in curly braces (e.g. "{title_legend:hide}").
     *
     * @return array<string, true> field names as keys, for fast lookups
     */
    public static function getPaletteFields(?string $palette): array
    {
        $fields = [];

        foreach (\preg_split('/[,;]/', (string) $palette) as $token) {
            $token = \trim($token);

            if ('' === $token || \str_starts_with($token, '{')) {
                continue;
            }

            $fields[$token] = true;
        }

        return $fields;
    }
}