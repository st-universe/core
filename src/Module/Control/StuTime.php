<?php

namespace Stu\Module\Control;

/**
 * This class adds the possibility to inject a timestamp generator
 */
class StuTime
{
    public const STU_YEARS_IN_FUTURE_OFFSET = 370;

    public function time(): int
    {
        return time();
    }

    public function transformToStuDate(int $unixTimestamp): string
    {
        return date("d.m.", $unixTimestamp) . (date("Y", $unixTimestamp) + self::STU_YEARS_IN_FUTURE_OFFSET) . " " . date("H:i", $unixTimestamp);
    }
}
