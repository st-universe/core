<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class ConditionCheckResult
{
    /** @var array<int> */
    private array $blockedShipIds = [];

    private bool $isLeaderBlocked = false;

    private InformationWrapper $informations;

    public function __construct(
        private LeaveFleetInterface $leaveFleet,
        private SpacecraftWrapperInterface $leader,
        private bool $isFixedFleetMode
    ) {
        $this->informations = new InformationWrapper();
    }

    public function addBlockedShip(SpacecraftInterface $spacecraft, string $reason): void
    {
        if ($this->isNotBlocked($spacecraft)) {
            $this->blockedShipIds[] = $spacecraft->getId();
            $this->informations->addInformation($reason);

            if ($this->isLeaderBlocked($spacecraft)) {
                $this->isLeaderBlocked = true;
            } elseif (
                $spacecraft instanceof ShipInterface
                && !$this->isFixedFleetMode
                && !$this->isLeaderBlocked
                && $spacecraft !== $this->leader->get()
            ) {
                $this->leaveFleet($spacecraft);
            }
        }
    }

    private function isLeaderBlocked(SpacecraftInterface $spacecraft): bool
    {
        return !$spacecraft instanceof ShipInterface
            || $spacecraft->isFleetLeader()
            || $spacecraft->getFleet() === null
            || $spacecraft === $this->leader->get();
    }

    public function isFlightPossible(): bool
    {
        if ($this->isLeaderBlocked) {
            return false;
        }

        return !$this->isFixedFleetMode || $this->blockedShipIds === [];
    }

    public function isNotBlocked(SpacecraftInterface $spacecraft): bool
    {
        return !in_array($spacecraft->getId(), $this->blockedShipIds);
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
