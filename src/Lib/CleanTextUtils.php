<?php

namespace Stu\Lib;

use RuntimeException;

class CleanTextUtils
{
    public static function clearEmojis(string $text): string
    {
        $text = (string) iconv('UTF-8', 'ISO-8859-15//IGNORE', $text);
        $text = (string) preg_replace('/\s+/', ' ', $text);

        return (string) iconv('ISO-8859-15', 'UTF-8', $text);
    }

    public static function clearUnicode(string $text): string
    {
        return preg_replace('/&#?\d+;?/', '', $text) ?? throw new RuntimeException(sprintf('Fehler beim Entfernen des Unicode aus: %s', $text));
    }

    /**
     * @param string $str
     */
    public static function checkBBCode($str): bool
    {
        $taglist = ["b", "i", "u", "color"]; //the bb-tags to search for

        foreach ($taglist as $tag) {
            // How often is the open tag?
            preg_match_all('/\[[ ]?' . $tag . '[ ]?(=[ ]?[^ ]+?[ ]?)?\]/i', $str, $matches);
            $opentags = count($matches['0']);

            // How often is the close tag?
            preg_match_all('/\[\/' . $tag . '\]/i', $str, $matches);
            $closetags = count($matches['0']);

            // how many tags have been unclosed?
            $diff = abs($opentags - $closetags);

            if ($diff > 0) {
                return false;
            }
        }

        return true;
    }
}
