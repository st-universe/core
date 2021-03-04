<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\ShipStateEnum;
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

    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        ShipCrewRepositoryInterface $shipCrewRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CrewRepositoryInterface $crewRepository,
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->shipCrewRepository = $shipCrewRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->crewRepository = $crewRepository;
        $this->astroEntryLib = $astroEntryLib;
    }

    public function leave(ShipInterface $ship): string
    {
        $this->shipSystemManager->deactivateAll($ship);

        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader($ship);
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        $ship->setAlertState(1);
        $ship->setDockedTo(null);
        $ship->setFleet(null);
        $this->shipRepository->save($ship);

        if ($ship->getRump()->isEscapePods()) {
            $this->letCrewDie($ship);
            return _('Die Rettungskapseln wurden zerstört, die Crew ist daher verstorben!');
        }

        //create pods entity
        $pods = $this->launchEscapePods($ship);

        if ($pods == null) {
            $this->letCrewDie($ship);
            return _('Keine Rettungskapseln vorhanden, die Crew ist daher verstorben!');
        }

        //transfer crew into pods
        $crewList = $ship->getCrewlist();
        foreach ($crewList as $shipCrew) {
            $shipCrew->setShip($pods);
            $this->shipCrewRepository->save($shipCrew);
        }

        return _('Die Crew hat das Schiff in den Rettungskapseln verlassen!');
    }

    private function letCrewDie(ShipInterface $ship): void
    {
        $crewArray = [];
        foreach ($ship->getCrewlist() as $shipCrew) {
            $crewArray[] = $shipCrew->getCrew();
        }

        $this->shipCrewRepository->truncateByShip((int) $ship->getId());

        foreach ($crewArray as $crew) {
            $this->crewRepository->delete($crew);
        }
    }

    private function launchEscapePods(ShipInterface $ship): ?ShipInterface
    {
        $shipRump = $this->shipRumpRepository->find($ship->getUser()->getFactionId() + 100);

        // faction does not have escape pods
        if ($shipRump == null) {
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

        //return to save place
        $this->returnToSafety($pods, $ship);

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

    private function returnToSafety(ShipInterface $pods, ShipInterface $ship)
    {
        $field = $pods->getCurrentMapField();

        if (
            $field->getFieldType()->getSpecialDamage()
            && (($pods->getSystem() !== null && $field->getFieldType()->getSpecialDamageInnerSystem()) || ($pods->getSystem() === null && !$field->getFieldType()->getSpecialDamageInnerSystem()))
        ) {
            $met = 'fly' . $ship->getFlightDirection();
            $this->$met($pods);
        }
    }

    //flee upwardss
    private function fly2(ShipInterface $pods)
    {
        $pods->setPosY($pods->getPosY() - 1);
    }

    //flee downwards
    private function fly4(ShipInterface $pods)
    {
        $pods->setPosY($pods->getPosY() + 1);
    }

    //flee right
    private function fly3(ShipInterface $pods)
    {
        $pods->setPosX($pods->getPosX() - 1);
    }

    //flee left
    private function fly1(ShipInterface $pods)
    {
        $pods->setPosX($pods->getPosX() + 1);
    }
}
