<?php

namespace Stu\Module\Tal;

use JBBCode\Parser;

interface TalHelperInterface
{
    public static function addPlusCharacter(string $value): string;

    public static function getBBCodeParser(): Parser;

    public static function jsquote(string $str): string;

    public static function formatSeconds(string $time): string;

    public static function getNumberWithThousandSeperator(int $number): string;
}
