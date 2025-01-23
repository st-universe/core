<?php

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

interface ActivatorDeactivatorHelperInterface
{
    public function activate(
        SpacecraftWrapperInterface|int $target,
        SpacecraftSystemTypeEnum $type,
        ConditionCheckResult|InformationInterface $logger,
        bool $allowUplink = false,
        bool $isDryRun = false
    ): bool;

    public function activateFleet(
        int $shipId,
        SpacecraftSystemTypeEnum $systemId,
        GameControllerInterface $game
    ): void;

    public function deactivate(
        SpacecraftWrapperInterface|int $shipId,
        spacecraftSystemTypeEnum $type,
        InformationInterface $informations,
        bool $allowUplink = false
    ): bool;

    public function deactivateFleet(
        ShipWrapperInterface|int $target,
        spacecraftSystemTypeEnum $type,
        InformationInterface $informations
    ): bool;

    public function setLssMode(
        int $shipId,
        SpacecraftLssModeEnum $lssMode,
        GameControllerInterface $game
    ): void;

    public function setAlertState(
        SpacecraftWrapperInterface|int $shipId,
        SpacecraftAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void;

    public function setAlertStateFleet(
        int $shipId,
        SpacecraftAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void;
}
