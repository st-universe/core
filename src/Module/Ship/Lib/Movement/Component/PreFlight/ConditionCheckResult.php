<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\PreFlight;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class ConditionCheckResult
{
    private LeaveFleetInterface $leaveFleet;

    private ShipWrapperInterface $leader;

    private bool $isFixedFleetMode;

    /** @var array<int> */
    private array $blockedShipIds = [];

    private bool $isLeaderBlocked = false;

    private InformationWrapper $informations;

    public function __construct(
        LeaveFleetInterface $leaveFleet,
        ShipWrapperInterface $leader,
        bool $isFixedFleetMode
    ) {
        $this->leaveFleet = $leaveFleet;
        $this->leader = $leader;
        $this->isFixedFleetMode = $isFixedFleetMode;
        $this->informations = new InformationWrapper();
    }

    public function addBlockedShip(ShipInterface $ship, string $reason): void
    {
        if ($this->isNotBlocked($ship)) {
            $this->blockedShipIds[] = $ship->getId();
            $this->informations->addInformation($reason);

            if ($this->isLeaderBlocked($ship)) {
                $this->isLeaderBlocked = true;
            } elseif (
                !$this->isFixedFleetMode
                && $ship !== $this->leader->get()
            ) {
                $this->leaveFleet($ship,);
            }
        }
    }

    private function isLeaderBlocked(ShipInterface $ship): bool
    {
        return $ship->isFleetLeader()
            || $ship->getFleet() === null
            || $ship === $this->leader->get();
    }

    public function isFlightPossible(): bool
    {
        if ($this->isLeaderBlocked) {
            return false;
        }

        return !$this->isFixedFleetMode || $this->blockedShipIds === [];
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
