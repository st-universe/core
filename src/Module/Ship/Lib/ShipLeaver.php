<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
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

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private MapRepositoryInterface $mapRepository;

    private CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipCrewRepositoryInterface $shipCrewRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        UserRepositoryInterface $userRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipSystemManagerInterface $shipSystemManager,
        CrewRepositoryInterface $crewRepository,
        AstroEntryLibInterface $astroEntryLib,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        MapRepositoryInterface $mapRepository,
        CancelColonyBlockOrDefendInterface $cancelColonyBlockOrDefend,
        EntityManagerInterface $entityManager
    ) {
        $this->shipCrewRepository = $shipCrewRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->userRepository = $userRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->crewRepository = $crewRepository;
        $this->astroEntryLib = $astroEntryLib;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->mapRepository = $mapRepository;
        $this->cancelColonyBlockOrDefend = $cancelColonyBlockOrDefend;
        $this->entityManager = $entityManager;
    }

    public function evacuate(ShipWrapperInterface $wrapper): string
    {
        $this->shipSystemManager->deactivateAll($wrapper);

        $ship = $wrapper->get();
        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader($ship);
        }

        if ($ship->getDockedShipCount() > 0) {
            $this->undockShips($ship);
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
        }

        $ship->setAlertStateGreen();
        $ship->setDockedTo(null);
        $ship->setFleet(null);
        $ship->setIsFleetLeader(false);
        $this->shipRepository->save($ship);

        if ($ship->getRump()->isEscapePods()) {
            $this->letCrewDie($ship);
            return _('Die Rettungskapseln wurden zerstört, die Crew ist daher verstorben!');
        }

        $podRump = $this->shipRumpRepository->find($ship->getUser()->getFactionId() + ShipRumpEnum::SHIP_RUMP_BASE_ID_ESCAPE_PODS);

        if ($podRump === null) {
            $this->letCrewDie($ship);
            return _('Keine Rettungskapseln vorhanden, die Crew ist daher verstorben!');
        }

        $this->escapeIntoPods($ship);

        return _('Die Crew hat das Schiff in den Rettungskapseln verlassen!');
    }

    private function escapeIntoPods(ShipInterface $ship): void
    {
        //create pods entity
        $pods = $this->launchEscapePods($ship);

        //transfer crew into pods
        //TODO not all...! depends on race config
        $crewList = $ship->getCrewlist();
        foreach ($crewList as $shipCrew) {
            $shipCrew->setShip($pods);
            $shipCrew->setSlot(null);
            $this->shipCrewRepository->save($shipCrew);
        }
    }

    public function dumpCrewman(ShipCrewInterface $shipCrew): string
    {
        $ship = $shipCrew->getShip();
        if ($ship === null) {
            throw new RuntimeException('can only dump crewman on ship');
        }

        //create pods entity
        $pods = $this->launchEscapePods($ship);

        if ($pods == null) {
            $crew = $shipCrew->getCrew();
            $this->shipCrewRepository->delete($shipCrew);
            $this->crewRepository->delete($crew);

            return _('Der Crewman wurde exekutiert!');
        }

        //transfer crewman into pods
        $shipCrew->setShip($pods);
        $shipCrew->setShipId($pods->getId());
        $ship->getCrewlist()->removeElement($shipCrew);
        $this->shipCrewRepository->save($shipCrew);

        return _('Der Crewman hat das Schiff in einer Rettungskapsel verlassen!');
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
        $shipRump = $this->shipRumpRepository->find($ship->getUser()->getFactionId() + ShipRumpEnum::SHIP_RUMP_BASE_ID_ESCAPE_PODS);

        // faction does not have escape pods
        if ($shipRump == null) {
            return null;
        }

        $pods = $this->shipRepository->prototype();
        $pods->setUser($this->userRepository->getFallbackUser());
        $pods->setRump($shipRump);
        $pods->setName(sprintf(_('Rettungskapseln von (%d)'), $ship->getId()));
        $pods->setHuell(1);
        $pods->setMaxHuell(1);
        $pods->setAlertStateGreen();

        $pods->updateLocation($ship->getMap(), $ship->getStarsystemMap());

        //return to save place
        $this->returnToSafety($pods, $ship);

        $this->shipRepository->save($pods);
        $this->entityManager->flush();
        return $pods;
    }

    private function changeFleetLeader(ShipInterface $oldLeader): void
    {
        $fleet = $oldLeader->getFleet();
        if ($fleet === null) {
            throw new RuntimeException('no fleet available');
        }

        $ship = current(
            array_filter(
                $fleet->getShips()->toArray(),
                fn(ShipInterface $ship): bool => $ship !== $oldLeader
            )
        );

        if (!$ship) {
            $this->cancelColonyBlockOrDefend->work($oldLeader, new InformationWrapper());
        }

        $oldLeader->setFleet(null);
        $oldLeader->setIsFleetLeader(false);
        $fleet->getShips()->removeElement($oldLeader);

        $this->shipRepository->save($oldLeader);

        if (!$ship) {
            $this->fleetRepository->delete($fleet);

            return;
        }

        $fleet->setLeadShip($ship);
        $ship->setIsFleetLeader(true);

        $this->shipRepository->save($ship);
        $this->fleetRepository->save($fleet);
    }

    private function undockShips(ShipInterface $ship): void
    {
        foreach ($ship->getDockedShips() as $dockedShip) {
            $dockedShip->setDockedTo(null);
            $this->shipRepository->save($dockedShip);
        }
    }

    private function returnToSafety(ShipInterface $pods, ShipInterface $ship): void
    {
        $field = $pods->getCurrentMapField();

        if (
            $field->getFieldType()->getSpecialDamage()
            && (($pods->getSystem() !== null && $field->getFieldType()->getSpecialDamageInnerSystem()) || ($pods->getSystem() === null && !$field->getFieldType()->getSpecialDamageInnerSystem()))
        ) {
            $met = 'fly' . $ship->getFlightDirection();
            $newXY = $this->$met($pods);

            if ($pods->getSystem() !== null) {
                $map = $this->starSystemMapRepository->getByCoordinates(
                    $pods->getSystem()->getId(),
                    $newXY[0],
                    $newXY[1]
                );
                $pods->setStarsystemMap($map);
            } else {
                $map = $this->mapRepository->getByCoordinates(
                    $pods->getLayerId(),
                    $newXY[0],
                    $newXY[1]
                );
                $pods->setMap($map);
            }
        }
    }

    //flee upwards
    /**
     * @return array<int>
     */
    private function fly2(ShipInterface $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() - 1];
    }

    //flee downwards
    /**
     * @return array<int>
     */
    private function fly4(ShipInterface $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() + 1];
    }

    //flee right
    /**
     * @return array<int>
     */
    private function fly3(ShipInterface $pods): array
    {
        return [$pods->getPosX() - 1, $pods->getPosY()];
    }

    //flee left
    /**
     * @return array<int>
     */
    private function fly1(ShipInterface $pods): array
    {
        return [$pods->getPosX() + 1, $pods->getPosY()];
    }
}
