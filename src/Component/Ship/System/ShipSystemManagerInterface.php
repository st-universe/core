<?php

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\Exception\SystemCooldownException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

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
     */
    public function deactivate(ShipInterface $ship, int $shipSystemId, bool $force = false): void;

    public function deactivateAll(ShipInterface $ship): void;

    public function getEnergyConsumption(int $shipSystemId): int;

    public function lookupSystem(int $shipSystemId): ShipSystemTypeInterface;

    public function handleDestroyedSystem(ShipWrapperInterface $wrapper, int $shipSystemId): void;

    public function handleDamagedSystem(ShipWrapperInterface $wrapper, int $shipSystemId): void;

    public function getActiveSystems(ShipInterface $ship, bool $sort = false): array;
}
