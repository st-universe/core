<?php

namespace Stu\Component\Ship\System;

use Stu\Orm\Entity\ShipInterface;

interface ShipSystemTypeInterface
{
    public function checkActivationConditions(ShipInterface $ship, &$reason): bool;

    public function checkDeactivationConditions(ShipInterface $ship, &$reason): bool;

    public function getEnergyUsageForActivation(): int;

    public function getEnergyConsumption(): int;

    /**
     * the higher the number, the more important the system is
     */
    public function getPriority(): int;

    public function activate(ShipInterface $ship, ShipSystemManagerInterface $manager): void;

    public function deactivate(ShipInterface $ship): void;

    public function handleDestruction(ShipInterface $ship): void;

    public function handleDamage(ShipInterface $ship): void;

    public function getDefaultMode(): int;

    public function getCooldownSeconds(): ?int;
}
