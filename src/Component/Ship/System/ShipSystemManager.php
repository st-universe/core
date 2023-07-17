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

    public function activate(ShipWrapperInterface $wrapper, int $shipSystemId, bool $force = false): void
    {
        $time = $this->stuTime->time();
        $system = $this->lookupSystem($shipSystemId);

        if (!$force) {
            $this->checkActivationConditions($wrapper, $system, $shipSystemId, $time);
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem !== null) {
            $epsSystem->lowerEps($system->getEnergyUsageForActivation())->update();
        }

        //cooldown
        $shipSystem = $wrapper->get()->getSystems()[$shipSystemId] ?? null;
        if ($shipSystem !== null && $system->getCooldownSeconds() !== null) {
            $shipSystem->setCooldown($time + $system->getCooldownSeconds());
        }

        $system->activate($wrapper, $this);
    }

    public function deactivate(ShipWrapperInterface $wrapper, int $shipSystemId, bool $force = false): void
    {
        $system = $this->lookupSystem($shipSystemId);

        if (!$force) {
            $this->checkDeactivationConditions($wrapper, $system, $shipSystemId);
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

    public function getEnergyConsumption(int $shipSystemId): int
    {
        $system = $this->lookupSystem($shipSystemId);

        return $system->getEnergyConsumption();
    }

    public function lookupSystem(int $shipSystemId): ShipSystemTypeInterface
    {
        $system = $this->systemList[$shipSystemId] ?? null;
        if ($system === null) {
            throw new InvalidSystemException();
        }

        return $system;
    }

    private function checkActivationConditions(
        ShipWrapperInterface $wrapper,
        ShipSystemTypeInterface $system,
        int $shipSystemId,
        int $time
    ): void {
        $ship = $wrapper->get();
        $shipSystem = $ship->getSystems()[$shipSystemId] ?? null;
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
        if (!$system->checkActivationConditions($ship, $reason)) {
            throw new ActivationConditionsNotMetException($reason);
        }
    }

    private function checkDeactivationConditions(
        ShipWrapperInterface $wrapper,
        ShipSystemTypeInterface $system,
        int $shipSystemId
    ): void {
        $ship = $wrapper->get();
        $shipSystem = $ship->getSystems()[$shipSystemId] ?? null;
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

    public function handleDestroyedSystem(ShipWrapperInterface $wrapper, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

        $system->handleDestruction($wrapper);
    }

    public function handleDamagedSystem(ShipWrapperInterface $wrapper, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

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
                    $prioArray[$system->getSystemType()] = $this->lookupSystem($system->getSystemType())->getPriority();
                }
            }
        }

        if ($sort) {
            usort(
                $activeSystems,
                function (ShipSystemInterface $a, ShipSystemInterface $b) use ($prioArray): int {
                    if ($prioArray[$a->getSystemType()] == $prioArray[$b->getSystemType()]) {
                        return 0;
                    }
                    return ($prioArray[$a->getSystemType()] < $prioArray[$b->getSystemType()]) ? -1 : 1;
                }
            );
        }

        return $activeSystems;
    }
}
