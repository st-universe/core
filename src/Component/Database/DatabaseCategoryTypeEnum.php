<?php

declare(strict_types=1);

namespace Stu\Component\Database;

use Stu\Component\Player\UserAwardEnum;

final class DatabaseCategoryTypeEnum
{

    public const DATABASE_CATEGORY_SHIPRUMP = 1;
    public const DATABASE_CATEGORY_RPGSHIP = 2;
    public const DATABASE_CATEGORY_TRADEPOST = 3;
    public const DATABASE_CATEGORY_REGION = 4;
    public const DATABASE_CATEGORY_PLANET_TYPE = 5;
    public const DATABASE_CATEGORY_STAR_SYSTEM_TYPE = 6;
    public const DATABASE_CATEGORY_STARSYSTEM = 7;
    public const DATABASE_CATEGORY_STATIONRUMP = 9;

    public const CATEGORY_TO_AWARD = [self::DATABASE_CATEGORY_STARSYSTEM => UserAwardEnum::COMPLETED_ASTRO];
}
