<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

final class SpacecraftSystemPriorities
{
    public const int PRIORITY_STANDARD = 1;
    public const array PRIORITIES = [
        SpacecraftSystemTypeEnum::SYSTEM_LIFE_SUPPORT->value => 10,
        SpacecraftSystemTypeEnum::SYSTEM_EPS->value => 6,
        SpacecraftSystemTypeEnum::SYSTEM_WARPCORE->value => 5,
        SpacecraftSystemTypeEnum::SYSTEM_FUSION_REACTOR->value => 5,
        SpacecraftSystemTypeEnum::SYSTEM_DEFLECTOR->value => 4,
        SpacecraftSystemTypeEnum::SYSTEM_TROOP_QUARTERS->value => 3,
        SpacecraftSystemTypeEnum::SYSTEM_WARPDRIVE->value => 3,
        SpacecraftSystemTypeEnum::SYSTEM_LSS->value => 2,
        SpacecraftSystemTypeEnum::SYSTEM_NBS->value => 2,
        SpacecraftSystemTypeEnum::SYSTEM_SUBSPACE_SCANNER->value => 0,
        SpacecraftSystemTypeEnum::SYSTEM_CLOAK->value => 0
    ];
}
