<?php

namespace Stu\Component\Spacecraft\System;

use Stu\Component\Spacecraft\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\AlreadyOffException;
use Stu\Component\Spacecraft\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\Exception\InvalidSystemException;
use Stu\Component\Spacecraft\System\Exception\SystemCooldownException;
use Stu\Component\Spacecraft\System\Exception\SystemDamagedException;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Entity\SpacecraftInterface;

interface SpacecraftSystemManagerInterface
{
    /**
     * @throws InsufficientEnergyException
     * @throws ActivationConditionsNotMetException
     * @throws InvalidSystemException
     * @throws SystemDamagedException
     * @throws SystemNotFoundException
     * @throws SystemCooldownException
     */
    public function activate(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $type,
        bool $force = false,
        bool $isDryRun = false
    ): void;

    /**
     * @throws InvalidSystemException
     * @throws SystemNotFoundException
     * @throws DeactivationConditionsNotMetException
     * @throws AlreadyOffException
     */
    public function deactivate(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $type,
        bool $force = false
    ): void;

    public function deactivateAll(SpacecraftWrapperInterface $wrapper): void;

    public function getEnergyUsageForActivation(SpacecraftSystemTypeEnum $type): int;

    public function getEnergyConsumption(SpacecraftSystemTypeEnum $type): int;

    public function lookupSystem(SpacecraftSystemTypeEnum $type): SpacecraftSystemTypeInterface;

    public function handleDestroyedSystem(SpacecraftWrapperInterface $wrapper, SpacecraftSystemTypeEnum $type): void;

    public function handleDamagedSystem(SpacecraftWrapperInterface $wrapper, SpacecraftSystemTypeEnum $type): void;

    /**
     * @return array<SpacecraftSystemInterface>
     */
    public function getActiveSystems(SpacecraftInterface $ship, bool $sort = false): array;
}
