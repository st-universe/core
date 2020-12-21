<?php

namespace Stu\Component\Ship\System;

use Stu\Orm\Entity\ShipInterface;

interface ShipSystemTypeInterface
{
    public function isAlreadyActive(ShipInterface $ship): bool;

    public function checkActivationConditions(ShipInterface $ship): bool;

    public function getEnergyUsageForActivation(): int;

    public function activate(ShipInterface $ship): void;

    public function deactivate(ShipInterface $ship): void;
}
