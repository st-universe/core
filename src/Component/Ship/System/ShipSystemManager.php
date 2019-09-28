<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Orm\Entity\ShipInterface;

final class ShipSystemManager implements ShipSystemManagerInterface
{

    /**
     * @var ShipSystemTypeInterface[]
     */
    private $systemList;

    public function __construct(
        array $systemList
    ) {
        $this->systemList = $systemList;
    }

    public function activate(ShipInterface $ship, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

        $this->checkConditions($ship, $system);

        $ship->setEps($ship->getEps() - $system->getEnergyUsageForActivation());

        $system->activate($ship);
    }

    public function deactivate(ShipInterface $ship, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

        $system->deactivate($ship);
    }

    private function lookupSystem(int $shipSystemId): ShipSystemTypeInterface
    {
        $system = $this->systemList[$shipSystemId] ?? null;
        if ($system === null){
            throw new InvalidSystemException();
        }
        return $system;
    }

    private function checkConditions(ShipInterface $ship, ShipSystemTypeInterface $system): void
    {
        if ($ship->getEps() < $system->getEnergyUsageForActivation()) {
            throw new InsufficientEnergyException();
        }
        if ($system->checkActivationConditions($ship) === false) {
            throw new ActivationConditionsNotMetException();
        }
    }
}
