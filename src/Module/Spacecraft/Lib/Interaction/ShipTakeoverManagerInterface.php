<?php

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface ShipTakeoverManagerInterface
{
    public const BOARDING_COOLDOWN_IN_SECONDS = 600;
    public const BOARDING_PRESTIGE_PER_TRY = 200;
    public const BOARDING_PRESTIGE_PER_MODULE_LEVEL = 25;
    public const TURNS_TO_TAKEOVER = 10;

    public function getPrestigeForBoardingAttempt(SpacecraftInterface $target): int;

    public function getPrestigeForTakeover(SpacecraftInterface $target): int;

    public function startTakeover(SpacecraftInterface $source, SpacecraftInterface $target, int $prestige): void;

    public function isTakeoverReady(ShipTakeoverInterface $takeover): bool;

    public function cancelTakeover(
        ?ShipTakeoverInterface $takeover,
        ?string $cause = null,
        bool $force = false
    ): void;

    public function cancelBothTakeover(
        SpacecraftInterface $spacecraft,
        ?string $passiveCause = null
    ): void;

    public function finishTakeover(ShipTakeoverInterface $takeover): void;
}
