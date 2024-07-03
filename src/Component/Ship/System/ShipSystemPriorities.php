<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

final class ShipSystemPriorities
{
    public const int PRIORITY_STANDARD = 1;
    public const array PRIORITIES = [
        ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT->value => 10,
        ShipSystemTypeEnum::SYSTEM_EPS->value => 6,
        ShipSystemTypeEnum::SYSTEM_WARPCORE->value => 5,
        ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR->value => 5,
        ShipSystemTypeEnum::SYSTEM_DEFLECTOR->value => 4,
        ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS->value => 3,
        ShipSystemTypeEnum::SYSTEM_WARPDRIVE->value => 3,
        ShipSystemTypeEnum::SYSTEM_LSS->value => 2,
        ShipSystemTypeEnum::SYSTEM_NBS->value => 2,
        ShipSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER->value => 0,
        ShipSystemTypeEnum::SYSTEM_CLOAK->value => 0
    ];
}
