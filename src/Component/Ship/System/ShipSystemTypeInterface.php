<?php

namespace Stu\Component\Ship\System;

use Stu\Orm\Entity\ShipInterface;

interface ShipSystemTypeInterface
{
    public function checkActivationConditions(ShipInterface $ship): bool;

    public function getEnergyUsageForActivation(): int;

    public function getDefaultMode(): int;

    public function activate(ShipInterface $ship): void;

    public function deactivate(ShipInterface $ship): void;
}
