<?php

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;

interface ActivatorDeactivatorHelperInterface
{
    public function activate(
        ShipWrapperInterface|int $shipId,
        ShipSystemTypeEnum $type,
        GameControllerInterface $game,
        bool $allowUplink = false
    ): bool;

    public function activateFleet(
        int $shipId,
        ShipSystemTypeEnum $systemId,
        GameControllerInterface $game
    ): void;

    public function deactivate(
        ShipWrapperInterface|int $shipId,
        shipSystemTypeEnum $type,
        GameControllerInterface $game,
        bool $allowUplink = false
    ): bool;

    public function deactivateFleet(
        int $shipId,
        shipSystemTypeEnum $type,
        GameControllerInterface $game
    ): void;

    public function setLSSMode(
        int $shipId,
        int $lssMode,
        GameControllerInterface $game
    ): void;

    public function setAlertState(
        int $shipId,
        int $alertState,
        GameControllerInterface $game
    ): void;

    public function setAlertStateFleet(
        int $shipId,
        int $alertState,
        GameControllerInterface $game
    ): void;

    public function setWarpSplitFleet(
        int $shipId,
        GameControllerInterface $game
    ): void;
}
