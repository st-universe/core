<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\AlreadyActiveException;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientCrewException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemCooldownException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotActivatableException;
use Stu\Component\Ship\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;

final class ShipSystemManager implements ShipSystemManagerInterface
{
    /** @var array<ShipSystemTypeInterface> */
    private array $systemList;

    private StuTime $stuTime;

    /**
     * @param array<ShipSystemTypeInterface> $systemList
     */
    public function __construct(
        array $systemList,
        StuTime $stuTime
    ) {
        $this->systemList = $systemList;
        $this->stuTime = $stuTime;
    }

    public function activate(
        ShipWrapperInterface $wrapper,
        ShipSystemTypeEnum $type,
        bool $force = false,
        bool $isDryRun = false
    ): void {
        $time = $this->stuTime->time();
        $system = $this->lookupSystem($type);

        if (!$force) {
            $this->checkActivationConditions($wrapper, $system, $type, $time);
        }

        if ($isDryRun) {
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem !== null) {
            $epsSystem->lowerEps($system->getEnergyUsageForActivation())->update();
        }

        //cooldown
        $shipSystem = $wrapper->get()->getSystems()[$type->value] ?? null;
        if ($shipSystem !== null && $system->getCooldownSeconds() !== null) {
            $shipSystem->setCooldown($time + $system->getCooldownSeconds());
        }

        $system->activate($wrapper, $this);
    }

    public function deactivate(ShipWrapperInterface $wrapper, ShipSystemTypeEnum $type, bool $force = false): void
    {
        $system = $this->lookupSystem($type);

        if (!$force) {
            $this->checkDeactivationConditions($wrapper, $system, $type);
        }

        $system->deactivate($wrapper);
    }

    public function deactivateAll(ShipWrapperInterface $wrapper): void
    {
        foreach ($wrapper->get()->getSystems() as $shipSystem) {
            try {
                $this->deactivate($wrapper, $shipSystem->getSystemType(), true);
            } catch (ShipSystemException $e) {
                continue;
            }
        }
    }

    public function getEnergyUsageForActivation(ShipSystemTypeEnum $type): int
    {
        $system = $this->lookupSystem($type);

        return $system->getEnergyUsageForActivation();
    }

    public function getEnergyConsumption(ShipSystemTypeEnum $type): int
    {
        $system = $this->lookupSystem($type);

        return $system->getEnergyConsumption();
    }

    public function lookupSystem(ShipSystemTypeEnum $type): ShipSystemTypeInterface
    {
        $system = $this->systemList[$type->value] ?? null;
        if ($system === null) {
            throw new InvalidSystemException();
        }

        return $system;
    }

    private function checkActivationConditions(
        ShipWrapperInterface $wrapper,
        ShipSystemTypeInterface $system,
        ShipSystemTypeEnum $type,
        int $time
    ): void {
        $ship = $wrapper->get();
        $shipSystem = $ship->getSystems()[$type->value] ?? null;
        if ($shipSystem === null) {
            throw new SystemNotFoundException();
        }

        if ($shipSystem->getStatus() === 0) {
            throw new SystemDamagedException();
        }

        $mode = $shipSystem->getMode();
        if ($mode === ShipSystemModeEnum::MODE_ALWAYS_OFF) {
            throw new SystemNotActivatableException();
        }

        if (
            $mode === ShipSystemModeEnum::MODE_ON
            ||  $mode === ShipSystemModeEnum::MODE_ALWAYS_ON
        ) {
            throw new AlreadyActiveException();
        }

        if (!$ship->hasEnoughCrew()) {
            throw new InsufficientCrewException();
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < $system->getEnergyUsageForActivation()) {
            throw new InsufficientEnergyException($system->getEnergyUsageForActivation());
        }

        $cooldown = $shipSystem->getCooldown();
        if ($cooldown !== null && $cooldown > $time) {
            throw new SystemCooldownException($cooldown - $time);
        }

        $reason = null;
        if (!$system->checkActivationConditions($wrapper, $reason)) {
            throw new ActivationConditionsNotMetException($reason);
        }
    }

    private function checkDeactivationConditions(
        ShipWrapperInterface $wrapper,
        ShipSystemTypeInterface $system,
        ShipSystemTypeEnum $type
    ): void {
        $ship = $wrapper->get();
        $shipSystem = $ship->getSystems()[$type->value] ?? null;
        if ($shipSystem === null) {
            throw new SystemNotFoundException();
        }

        $mode = $shipSystem->getMode();
        if ($mode === ShipSystemModeEnum::MODE_ALWAYS_ON) {
            throw new SystemNotDeactivatableException();
        }

        if (
            $mode === ShipSystemModeEnum::MODE_OFF
            ||  $mode === ShipSystemModeEnum::MODE_ALWAYS_OFF
        ) {
            throw new AlreadyOffException();
        }

        $reason = null;
        if (!$system->checkDeactivationConditions($wrapper, $reason)) {
            throw new DeactivationConditionsNotMetException($reason);
        }
    }

    public function handleDestroyedSystem(ShipWrapperInterface $wrapper, ShipSystemTypeEnum $type): void
    {
        $system = $this->lookupSystem($type);

        $system->handleDestruction($wrapper);
    }

    public function handleDamagedSystem(ShipWrapperInterface $wrapper, ShipSystemTypeEnum $type): void
    {
        $system = $this->lookupSystem($type);

        $system->handleDamage($wrapper);
    }

    public function getActiveSystems(ShipInterface $ship, bool $sort = false): array
    {
        $activeSystems = [];
        $prioArray = [];
        foreach ($ship->getSystems() as $system) {
            if ($system->getMode() > 1) {
                $activeSystems[] = $system;
                if ($sort) {
                    $prioArray[$system->getSystemType()->value] = $this->lookupSystem($system->getSystemType())->getPriority();
                }
            }
        }

        if ($sort) {
            usort(
                $activeSystems,
                fn (ShipSystemInterface $a, ShipSystemInterface $b): int => $prioArray[$a->getSystemType()->value] <=> $prioArray[$b->getSystemType()->value]
            );
        }

        return $activeSystems;
    }
}
