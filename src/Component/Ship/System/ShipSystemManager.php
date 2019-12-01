<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientCrewException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Orm\Entity\ShipInterface;

final class ShipSystemManager implements ShipSystemManagerInterface
{

    /**
     * @var ShipSystemTypeInterface[]
     */
    private array $systemList;

    public function __construct(
        array $systemList
    ) {
        $this->systemList = $systemList;
    }

    public function activate(ShipInterface $ship, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

        $this->checkConditions($ship, $system, $shipSystemId);

        $ship->setEps($ship->getEps() - $system->getEnergyUsageForActivation());

        $system->activate($ship);
    }

    public function deactivate(ShipInterface $ship, int $shipSystemId): void
    {
        $system = $this->lookupSystem($shipSystemId);

        $system->deactivate($ship);
    }

    public function deactivateAll(ShipInterface $ship): void
    {
        $ship->deactivateTraktorBeam();

        foreach ($ship->getSystems() as $shipSystem) {
            try {
                $this->deactivate($ship, $shipSystem->getSystemType());
            } catch (ShipSystemException $e) {
                continue;
            }
        }
    }

    private function lookupSystem(int $shipSystemId): ShipSystemTypeInterface
    {
        $system = $this->systemList[$shipSystemId] ?? null;
        if ($system === null){
            throw new InvalidSystemException();
        }

        return $system;
    }

    private function checkConditions(
        ShipInterface $ship,
        ShipSystemTypeInterface $system,
        int $shipSystemId
    ): void {
        $shipSystem = $ship->getSystems()[$shipSystemId] ?? null;
        if ($shipSystem === null) {
            throw new SystemNotFoundException();
        }

        if ($shipSystem->isActivateable() === false) {
            throw new SystemDamagedException();
        }

        if ($ship->getEps() < $system->getEnergyUsageForActivation()) {
            throw new InsufficientEnergyException();
        }

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() === 0) {
            throw new InsufficientCrewException();
        }

        if ($system->checkActivationConditions($ship) === false) {
            throw new ActivationConditionsNotMetException();
        }
    }
}
