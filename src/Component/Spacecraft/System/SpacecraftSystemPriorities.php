<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

final class SpacecraftSystemPriorities
{
    public const int PRIORITY_STANDARD = 1;
    public const array PRIORITIES = [
        SpacecraftSystemTypeEnum::LIFE_SUPPORT->value => 10,
        SpacecraftSystemTypeEnum::EPS->value => 6,
        SpacecraftSystemTypeEnum::WARPCORE->value => 5,
        SpacecraftSystemTypeEnum::FUSION_REACTOR->value => 5,
        SpacecraftSystemTypeEnum::DEFLECTOR->value => 4,
        SpacecraftSystemTypeEnum::TROOP_QUARTERS->value => 3,
        SpacecraftSystemTypeEnum::WARPDRIVE->value => 3,
        SpacecraftSystemTypeEnum::LSS->value => 2,
        SpacecraftSystemTypeEnum::NBS->value => 2,
        SpacecraftSystemTypeEnum::SUBSPACE_SCANNER->value => 0,
        SpacecraftSystemTypeEnum::CLOAK->value => 0
    ];
}
