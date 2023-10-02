<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight;

use Stu\Lib\InformationWrapper;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Orm\Entity\ShipInterface;

class ConditionCheckResult
{
    private LeaveFleetInterface $leaveFleet;

    private bool $isFixedFleetMode;

    /** @var array<int> */
    private array $blockedShipIds = [];

    private bool $isFleetLeaderBlocked = false;

    private InformationWrapper $informations;

    public function __construct(LeaveFleetInterface $leaveFleet, bool $isFixedFleetMode)
    {
        $this->leaveFleet = $leaveFleet;
        $this->isFixedFleetMode = $isFixedFleetMode;
        $this->informations = new InformationWrapper();
    }

    public function addBlockedShip(ShipInterface $ship, string $reason): void
    {
        if ($this->isNotBlocked($ship)) {
            $this->blockedShipIds[] = $ship->getId();
            $this->informations->addInformation($reason);

            if ($ship->isFleetLeader()) {
                $this->isFleetLeaderBlocked = true;
            } elseif (!$this->isFixedFleetMode) {
                $this->leaveFleet($ship,);
            }
        }
    }

    public function isFlightPossible(): bool
    {
        if ($this->isFleetLeaderBlocked) {
            return false;
        }

        return !$this->isFixedFleetMode || empty($this->blockedShipIds);
    }

    public function isNotBlocked(ShipInterface $ship): bool
    {
        return !in_array($ship->getId(), $this->blockedShipIds);
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
