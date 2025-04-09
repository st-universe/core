<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\Movement\FlightCompany;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class ConditionCheckResult
{
    /** @var array<int> */
    private array $blockedSpacecraftIds = [];

    private bool $isLeaderBlocked = false;

    private InformationWrapper $informations;

    public function __construct(
        private LeaveFleetInterface $leaveFleet,
        private FlightCompany $flightCompany
    ) {
        $this->informations = new InformationWrapper();
    }

    public function addBlockedShip(SpacecraftInterface $spacecraft, string $reason): void
    {
        if (!$this->isBlocked($spacecraft)) {
            $this->blockedSpacecraftIds[] = $spacecraft->getId();
            $this->informations->addInformation($reason);

            $isLeader = $spacecraft === $this->flightCompany->getLeader();
            if ($isLeader) {
                $this->isLeaderBlocked = true;
            } elseif (
                $spacecraft instanceof ShipInterface
                && !$this->flightCompany->isFixedFleetMode()
                && !$this->isLeaderBlocked
            ) {
                $this->leaveFleet($spacecraft);
            }
        }
    }

    public function isFlightPossible(): bool
    {
        if ($this->isLeaderBlocked) {
            return false;
        }

        return !$this->flightCompany->isFixedFleetMode() || $this->blockedSpacecraftIds === [];
    }

    /** @return array<int> */
    public function getBlockedIds(): array
    {
        return $this->blockedSpacecraftIds;
    }

    public function isBlocked(SpacecraftInterface $spacecraft): bool
    {
        return in_array($spacecraft->getId(), $this->blockedSpacecraftIds);
    }

    /** @return array<string> */
    public function getInformations(): array
    {
        return $this->informations->getInformations();
    }

    private function leaveFleet(ShipInterface $ship): void
    {
        $this->leaveFleet->leaveFleet($ship);

        $this->informations->addInformation(sprintf(
            _('Die %s hat die Flotte verlassen (%d|%d)'),
            $ship->getName(),
            $ship->getPosX(),
            $ship->getPosY()
        ));
    }
}
