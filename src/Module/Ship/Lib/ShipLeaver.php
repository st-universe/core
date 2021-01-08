<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShipLeaver implements ShipLeaverInterface
{
    private ShipCrewRepositoryInterface $shipCrewRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private UserRepositoryInterface $userRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ShipSystemManagerInterface $shipSystemManager;
    
    private CrewRepositoryInterface $crewRepository;

    public function __construct(
        ShipCrewRepositoryInterface $shipCrewRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CrewRepositoryInterface $crewRepository
    ) {
        $this->shipCrewRepository = $shipCrewRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->crewRepository = $crewRepository;
    }

    public function leave(ShipInterface $ship): string
    {
        $this->shipSystemManager->deactivateAll($ship);

        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader($ship);
        }

        $ship->setAlertState(1);
        $ship->setDockedTo(null);
        $ship->setFleet(null);

        if ($ship->getRump()->isEscapePods())
        {
            $this->letCrewDie($ship);
            return _('Die Rettungskapseln wurden zerstört, die Crew ist daher verstorben!');
        }

        //create pods entity
        $pods = $this->launchEscapePods($ship);

        if ($pods == null)
        {
            $this->letCrewDie($ship);
            return _('Keine Rettungskapseln vorhanden, die Crew ist daher verstorben!');
        }
        
        //transfer crew into pods
        $crewList = $ship->getCrewlist();
        foreach ($crewList as $shipCrew)
        {
            $shipCrew->setShip($pods);
            $this->shipCrewRepository->save($shipCrew);
        }
        $ship->getCrewlist()->clear();
        
        $this->shipRepository->save($pods);
        $this->shipRepository->save($ship);
        return _('Die Crew hat das Schiff in den Rettungskapseln verlassen!');
    }

    private function letCrewDie(ShipInterface $ship): void
    {
        $this->shipCrewRepository->truncateByShip((int) $ship->getId());
        
        foreach ($ship->getCrewlist() as $shipCrew)
        {
            $this->crewRepository->delete($shipCrew->getCrew());
        }

    }
    
    private function launchEscapePods(ShipInterface $ship): ?ShipInterface
    {
        $shipRump = $this->shipRumpRepository->find($ship->getUser()->getFactionId() + 100);
        
        // faction does not have escape pods
        if ($shipRump == null)
        {
            return null;
        }

        $pods = $this->shipRepository->prototype();
        $pods->setUser($this->userRepository->find(GameEnum::USER_NOONE));
        $pods->setRump($shipRump);
        $pods->setName(sprintf(_('Rettungskapseln von (%d)'), $ship->getId()));
        $pods->setHuell(1);
        $pods->setMaxHuell(1);

        $pods->setSX($ship->getSx());
        $pods->setSY($ship->getSy());
        $pods->setSystem($ship->getSystem());
        $pods->setCX($ship->getCx());
        $pods->setCY($ship->getCy());
        
        $this->shipRepository->save($pods);
        return $pods;
    }
    
    private function changeFleetLeader(ShipInterface $obj): void
    {
        $ship = current(
            array_filter(
                $obj->getFleet()->getShips()->toArray(),
                function (ShipInterface $ship) use ($obj): bool {
                    return $ship !== $obj;
                }
            )
        );

        $fleet = $obj->getFleet();

        $obj->setFleet(null);
        $fleet->getShips()->removeElement($obj);

        $this->shipRepository->save($obj);

        if (!$ship) {
            $this->fleetRepository->delete($fleet);

            return;
        }
        $fleet->setLeadShip($ship);

        $this->fleetRepository->save($fleet);
    }
}
