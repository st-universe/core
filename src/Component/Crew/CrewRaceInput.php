<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

final class CrewRaceInput
{
    public static function isValidDescription(string $description): bool
    {
        return mb_strlen($description) <= 255
            && preg_match("/^\\p{Lu}[\\p{L}'`]*(?: [\\p{L}'`]+)*$/u", $description) === 1
            && preg_match("/['`]{2}/", $description) !== 1;
    }

    public static function normalizeDefine(string $define): string
    {
        $define = strtr($define, [
            'Ä' => 'AE',
            'Ö' => 'OE',
            'Ü' => 'UE',
            'ä' => 'AE',
            'ö' => 'OE',
            'ü' => 'UE',
            'ß' => 'SS'
        ]);
        $define = preg_replace('/\s+/u', '_', $define) ?? '';
        $define = mb_strtoupper($define);
        $define = preg_replace('/[^A-Z_]/', '', $define) ?? '';
        $define = preg_replace('/_+/', '_', $define) ?? '';

        return trim($define, '_');
    }

    public static function isValidDefine(string $define): bool
    {
        return mb_strlen($define) <= 255
            && preg_match('/^[A-Z]+(?:_[A-Z]+)*$/', $define) === 1;
    }
}
