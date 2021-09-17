<?php

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;

interface ActivatorDeactivatorHelperInterface
{
    public function activate(
        int $shipId,
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
        int $shipId,
        int $systemId,
        GameControllerInterface $game,
        bool $allowUplink = false
    ): void;

    public function deactivateFleet(
        int $shipId,
        int $systemId,
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
}
