<?php

namespace Stu\Module\Control;

use DateTime;

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

    public function date(string $format): string
    {
        return date($format);
    }

    public function dateTime(): DateTime
    {
        return new DateTime();
    }

    public function transformToStuDate(int $unixTimestamp): string
    {
        return sprintf(
            '%s%s',
            date('d.m.', $unixTimestamp),
            (int)date("Y", $unixTimestamp) + StuTime::STU_YEARS_IN_FUTURE_OFFSET,
        );
    }

    public function transformToStuDateTime(int $unixTimestamp): string
    {
        return sprintf(
            '%s %s',
            $this->transformToStuDate($unixTimestamp),
            date("H:i", $unixTimestamp)
        );
    }
}
