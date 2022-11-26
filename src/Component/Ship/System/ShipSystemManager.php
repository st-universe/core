<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\ShipSystemModeEnum;
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
use Stu\Orm\Entity\ShipInterface;

final class ShipSystemManager implements ShipSystemManagerInterface
{

    /**
     * @var ShipSystemTypeInterface[]
     */
    private array $systemList;

    public function __construct(
        array $systemList
    ) {
        $this->systemList = $systemList;
    }

    public function activate(ShipInterface $ship, int $shipSystemId, bool $force = false): void
    {
        $system = $this->lookupSystem($shipSystemId);

        if (!$force) {
            $this->checkActivationConditions($ship, $system, $shipSystemId);
        }
        $ship->setEps($ship->getEps() - $system->getEnergyUsageForActivation());

        //cooldown
        $shipSystem = $ship->getSystems()[$shipSystemId] ?? null;
        if ($shipSystem !== null && $system->getCooldownSeconds() !== null) {
            $shipSystem->setCooldown(time() + $system->getCooldownSeconds());
        }

        $system->activate($ship);
    }

    public function deactivate(ShipInterface $ship, int $shipSystemId, bool $force = false): void
    {
        $system = $this->lookupSystem($shipSystemId);

        if (!$force) {
            $this->checkDeactivationConditions($ship, $system, $shipSystemId);
        }

        $system->deactivate($ship);
    }

    public function deactivateAll(ShipInterface $ship): void
    {
        foreach ($ship->getSystems() as $shipSystem) {
            try {
                $this->deactivate($ship, $shipSystem->getSystemType(), true);
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
        ShipInterface $ship,
        ShipSystemTypeInterface $system,
        int $shipSystemId
    ): void {
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

        if ($ship->getEps() < $system->getEnergyUsageForActivation()) {
            throw new InsufficientEnergyException($system->getEnergyUsageForActivation());
        }

        $cooldown = $shipSystem->getCooldown();
        if ($cooldown !== null && $cooldown > time()) {
            throw new SystemCooldownException($cooldown - time());
        }

        $reason = null;
        if (!$system->checkActivationConditions($ship, $reason)) {
            throw new ActivationConditionsNotMetException($reason);
        }
    }

    private function checkDeactivationConditions(
        ShipInterface $ship,
        ShipSystemTypeInterface $system,
        int $shipSystemId
    ): void {
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
        if (!$system->checkDeactivationConditions($ship, $reason)) {
            throw new DeactivationConditionsNotMetException($reason);
        }
    }

    public function handleDestroyedSystem(ShipInterface $ship, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

        $system->handleDestruction($ship);
    }

    public function handleDamagedSystem(ShipInterface $ship, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

        $system->handleDamage($ship);
    }
}
