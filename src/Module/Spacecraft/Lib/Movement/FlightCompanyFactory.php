<?php

namespace Stu\Module\Spacecraft\Lib\Movement;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\PreFlightConditionsCheckInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class FlightCompanyFactory
{
    public function __construct(private PreFlightConditionsCheckInterface $preFlightConditionsCheck) {}

    public function create(SpacecraftWrapperInterface $leadWrapper): FlightCompany
    {
        $subject = $this->getSubject($leadWrapper);

        return new FlightCompany(
            $subject,
            $this->getMembers($subject),
            $this->preFlightConditionsCheck
        );
    }

    private function getSubject(SpacecraftWrapperInterface $leadWrapper): SpacecraftWrapperInterface|FleetWrapperInterface
    {
        $fleetWrapper = $leadWrapper->getFleetWrapper();
        if ($fleetWrapper === null) {
            return $leadWrapper;
        }

        $fleetLeader = $fleetWrapper->getLeadWrapper()->get();

        //TODO check for isFleetLeader too?
        return $fleetLeader === $leadWrapper->get()
            ? $fleetWrapper
            : $leadWrapper;
    }

    /** @return Collection<int, covariant SpacecraftWrapperInterface> */
    private function getMembers(SpacecraftWrapperInterface|FleetWrapperInterface $subject): Collection
    {
        return $subject instanceof FleetWrapperInterface
            ? $subject->getShipWrappers()
            : new ArrayCollection([$subject->get()->getId() => $subject]);
    }
}
