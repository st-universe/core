<?php

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\Exception\SystemCooldownException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;

interface ShipSystemManagerInterface
{
    /**
     * @throws InsufficientEnergyException
     * @throws ActivationConditionsNotMetException
     * @throws InvalidSystemException
     * @throws SystemDamagedException
     * @throws SystemNotFoundException
     * @throws SystemCooldownException
     */
    public function activate(ShipWrapperInterface $wrapper, int $shipSystemId, bool $force = false): void;

    /**
     * @throws InvalidSystemException
     * @throws SystemNotFoundException
     * @throws DeactivationConditionsNotMetException
     * @throws AlreadyOffException
     */
    public function deactivate(ShipWrapperInterface $wrapper, int $shipSystemId, bool $force = false): void;

    public function deactivateAll(ShipWrapperInterface $wrapper): void;

    public function getEnergyUsageForActivation(int $shipSystemId): int;

    public function getEnergyConsumption(int $shipSystemId): int;

    public function lookupSystem(int $shipSystemId): ShipSystemTypeInterface;

    public function handleDestroyedSystem(ShipWrapperInterface $wrapper, int $shipSystemId): void;

    public function handleDamagedSystem(ShipWrapperInterface $wrapper, int $shipSystemId): void;

    /**
     * @return array<ShipSystemInterface>
     */
    public function getActiveSystems(ShipInterface $ship, bool $sort = false): array;
}
