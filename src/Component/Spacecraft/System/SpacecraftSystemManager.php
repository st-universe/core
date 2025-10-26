<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\AlreadyActiveException;
use Stu\Component\Spacecraft\System\Exception\AlreadyOffException;
use Stu\Component\Spacecraft\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\InsufficientCrewException;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\Exception\InvalidSystemException;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\Exception\SystemCooldownException;
use Stu\Component\Spacecraft\System\Exception\SystemDamagedException;
use Stu\Component\Spacecraft\System\Exception\SystemNotActivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Spacecraft;

final class SpacecraftSystemManager implements SpacecraftSystemManagerInterface
{
    /**
     * @param array<SpacecraftSystemTypeInterface> $systemList
     */
    public function __construct(private array $systemList, private StuTime $stuTime) {}

    #[\Override]
    public function activate(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $type,
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

        $this->setCooldown($wrapper, $type, $time, $system);

        $system->activate($wrapper, $this);
    }

    private function setCooldown(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $type,
        int $time,
        SpacecraftSystemTypeInterface $system
    ): void {
        $shipSystem = $wrapper->get()->getSystems()[$type->value] ?? null;
        if ($shipSystem !== null && $system->getCooldownSeconds() !== null) {
            $shipSystem->setCooldown($time + $system->getCooldownSeconds());
        }
    }

    #[\Override]
    public function deactivate(SpacecraftWrapperInterface $wrapper, SpacecraftSystemTypeEnum $type, bool $force = false): void
    {
        $system = $this->lookupSystem($type);

        if (!$force) {
            $this->checkDeactivationConditions($wrapper, $system, $type);
        }

        $system->deactivate($wrapper);
    }

    #[\Override]
    public function deactivateAll(SpacecraftWrapperInterface $wrapper): void
    {
        foreach ($wrapper->get()->getSystems() as $shipSystem) {
            try {
                $this->deactivate($wrapper, $shipSystem->getSystemType(), true);
            } catch (SpacecraftSystemException) {
                continue;
            }
        }
    }

    #[\Override]
    public function getEnergyUsageForActivation(SpacecraftSystemTypeEnum $type): int
    {
        $system = $this->lookupSystem($type);

        return $system->getEnergyUsageForActivation();
    }

    #[\Override]
    public function getEnergyConsumption(SpacecraftSystemTypeEnum $type): int
    {
        $system = $this->lookupSystem($type);

        return $system->getEnergyConsumption();
    }

    #[\Override]
    public function lookupSystem(SpacecraftSystemTypeEnum $type): SpacecraftSystemTypeInterface
    {
        $system = $this->systemList[$type->value] ?? null;
        if ($system === null) {
            throw new InvalidSystemException();
        }

        return $system;
    }

    private function checkActivationConditions(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeInterface $system,
        SpacecraftSystemTypeEnum $type,
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
        if ($mode === SpacecraftSystemModeEnum::MODE_ALWAYS_OFF) {
            throw new SystemNotActivatableException();
        }

        if ($mode->isActivated()) {
            throw new AlreadyActiveException();
        }

        if (!$ship->hasEnoughCrew() && !$system->canBeActivatedWithInsufficientCrew()) {
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

        $reason = '';
        if (!$system->checkActivationConditions($wrapper, $reason)) {
            throw new ActivationConditionsNotMetException($reason);
        }
    }

    private function checkDeactivationConditions(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeInterface $system,
        SpacecraftSystemTypeEnum $type
    ): void {
        $ship = $wrapper->get();
        $shipSystem = $ship->getSystems()[$type->value] ?? null;
        if ($shipSystem === null) {
            throw new SystemNotFoundException();
        }

        $mode = $shipSystem->getMode();
        if ($mode === SpacecraftSystemModeEnum::MODE_ALWAYS_ON) {
            throw new SystemNotDeactivatableException();
        }

        if (
            $mode === SpacecraftSystemModeEnum::MODE_OFF
            ||  $mode === SpacecraftSystemModeEnum::MODE_ALWAYS_OFF
        ) {
            throw new AlreadyOffException();
        }

        $reason = '';
        if (!$system->checkDeactivationConditions($wrapper, $reason)) {
            throw new DeactivationConditionsNotMetException($reason);
        }
    }

    #[\Override]
    public function handleDestroyedSystem(SpacecraftWrapperInterface $wrapper, SpacecraftSystemTypeEnum $type): void
    {
        $system = $this->lookupSystem($type);

        $system->handleDestruction($wrapper);
    }

    #[\Override]
    public function handleDamagedSystem(SpacecraftWrapperInterface $wrapper, SpacecraftSystemTypeEnum $type): void
    {
        $system = $this->lookupSystem($type);

        $system->handleDamage($wrapper);
    }

    #[\Override]
    public function getActiveSystems(Spacecraft $ship, bool $sort = false): Collection
    {
        $activeSystems = [];
        $prioArray = [];
        foreach ($ship->getSystems() as $system) {
            if ($system->getMode()->isActivated()) {
                $activeSystems[] = $system;
                if ($sort) {
                    $prioArray[$system->getSystemType()->value] = $system->getSystemType()->getPriority();
                }
            }
        }

        if ($sort) {
            usort(
                $activeSystems,
                fn(SpacecraftSystem $a, SpacecraftSystem $b): int => $prioArray[$a->getSystemType()->value] <=> $prioArray[$b->getSystemType()->value]
            );
        }

        return new ArrayCollection($activeSystems);
    }
}
