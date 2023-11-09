<?php

namespace Stu\Component\Ship\System;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface ShipSystemTypeInterface
{
    public function setShip(ShipInterface $ship): void;

    public function checkActivationConditions(ShipWrapperInterface $wrapper, ?string &$reason): bool;

    public function checkDeactivationConditions(ShipWrapperInterface $wrapper, ?string &$reason): bool;

    public function getEnergyUsageForActivation(): int;

    public function getEnergyConsumption(): int;

    /**
     * the higher the number, the more important the system is
     */
    public function getPriority(): int;

    public function activate(ShipWrapperInterface $wrapper, ShipSystemManagerInterface $manager): void;

    public function deactivate(ShipWrapperInterface $wrapper): void;

    public function handleDestruction(ShipWrapperInterface $wrapper): void;

    public function handleDamage(ShipWrapperInterface $wrapper): void;

    public function getDefaultMode(): int;

    public function getCooldownSeconds(): ?int;
}
