<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\Spacecraft;

interface ShipTakeoverManagerInterface
{
    public const BOARDING_COOLDOWN_IN_SECONDS = 600;
    public const BOARDING_PRESTIGE_PER_TRY = 200;
    public const BOARDING_PRESTIGE_PER_MODULE_LEVEL = 25;
    public const TURNS_TO_TAKEOVER = 10;

    public function getPrestigeForBoardingAttempt(Spacecraft $target): int;

    public function getPrestigeForTakeover(Spacecraft $target): int;

    public function startTakeover(Spacecraft $source, Spacecraft $target, int $prestige): void;

    public function isTakeoverReady(ShipTakeover $takeover): bool;

    public function cancelTakeover(
        ?ShipTakeover $takeover,
        ?string $cause = null,
        bool $force = false
    ): void;

    public function cancelBothTakeover(
        Spacecraft $spacecraft,
        ?string $passiveCause = null
    ): void;

    public function finishTakeover(ShipTakeover $takeover): void;
}
