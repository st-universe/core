<?php

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Orm\Entity\ShipInterface;

interface ShipSystemManagerInterface
{
    /**
     * @throws InsufficientEnergyException
     * @throws ActivationConditionsNotMetException
     * @throws InvalidSystemException
     */
    public function activate(ShipInterface $ship, int $shipSystemId): void;

    public function deactivate(ShipInterface $ship, int $shipSystemId): void;
}
