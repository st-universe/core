<?php

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\Exception\SystemCooldownException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemTypeInterface;
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
    public function activate(ShipInterface $ship, int $shipSystemId, bool $force = false): void;

    /**
     * @throws InvalidSystemException
     * @throws SystemNotFoundException
     * @throws DeactivationConditionsNotMetException
     */
    public function deactivate(ShipInterface $ship, int $shipSystemId, bool $force = false): void;

    public function deactivateAll(ShipInterface $ship): void;

    public function getEnergyConsumption(int $shipSystemId): int;

    public function lookupSystem(int $shipSystemId): ShipSystemTypeInterface;

    public function handleDestroyedSystem(ShipInterface $ship, int $shipSystemId): void;

    public function handleDamagedSystem(ShipInterface $ship, int $shipSystemId): void;

    public function getActiveSystems(ShipInterface $ship, bool $sort = false): array;
}
