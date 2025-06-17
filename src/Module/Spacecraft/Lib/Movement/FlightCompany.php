<?php

namespace Stu\Module\Spacecraft\Lib\Movement;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class FlightCompany
{
    /** @param Collection<int, covariant SpacecraftWrapperInterface> $members */
    public function __construct(
        private SpacecraftWrapperInterface|FleetWrapperInterface $subject,
        private Collection $members,
        private PreFlightConditionsCheckInterface $preFlightConditionsCheck
    ) {}

    public function getLeader(): SpacecraftInterface
    {
        return $this->getLeadWrapper()->get();
    }

    public function getLeadWrapper(): SpacecraftWrapperInterface
    {
        return $this->subject instanceof SpacecraftWrapperInterface
            ? $this->subject
            : $this->subject->getLeadWrapper();
    }

    /** @return Collection<int, SpacecraftWrapperInterface> */
    public function getActiveMembers(): Collection
    {
        return $this->members
            ->filter(fn(SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());
    }

    public function isEmpty(): bool
    {
        return $this->getActiveMembers()->isEmpty();
    }

    public function isEverybodyDestroyed(): bool
    {
        return
            !$this->members->isEmpty()
            && !$this->members->exists(fn(int $key, SpacecraftWrapperInterface $wrapper): bool => !$wrapper->get()->isDestroyed());
    }

    public function isFleetMode(): bool
    {
        return $this->subject instanceof FleetWrapperInterface;
    }

    public function isFixedFleetMode(): bool
    {
        return $this->subject instanceof FleetWrapperInterface
            && $this->subject->get()->isFleetFixed();
    }

    public function hasToLeaveFleet(): bool
    {
        return $this->subject instanceof SpacecraftWrapperInterface
            && $this->subject->getFleetWrapper() !== null;
    }

    public function isFlightPossible(
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): bool {
        // check all flight pre conditions
        $conditionCheckResult = $this->preFlightConditionsCheck->checkPreconditions(
            $this,
            $flightRoute,
            $messages
        );

        $this->removeBlocked($conditionCheckResult);

        return $conditionCheckResult->isFlightPossible();
    }

    private function removeBlocked(ConditionCheckResult $conditionCheckResult): void
    {
        foreach ($conditionCheckResult->getBlockedIds() as $spacecraftId) {
            $this->members->remove($spacecraftId);
        }
    }
}
