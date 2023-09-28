<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;

interface ActivatorDeactivatorHelperInterface
{
    public function activate(
        ShipWrapperInterface|int $shipId,
        int $systemId,
        GameControllerInterface $game,
        bool $allowUplink = false
    ): bool;

    public function activateFleet(
        int $shipId,
        int $systemId,
        GameControllerInterface $game
    ): void;

    public function deactivate(
        ShipWrapperInterface|int $shipId,
        int $systemId,
        GameControllerInterface $game,
        bool $allowUplink = false
    ): bool;

    public function deactivateFleet(
        int $shipId,
        int $systemId,
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
