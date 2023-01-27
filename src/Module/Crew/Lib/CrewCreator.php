<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use Stu\Component\Crew\CrewEnum;
use Stu\Component\Crew\CrewOriginException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipCrewInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CrewCreator implements CrewCreatorInterface
{
    private CrewRaceRepositoryInterface $crewRaceRepository;

    private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private CrewRepositoryInterface $crewRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        CrewRaceRepositoryInterface $crewRaceRepository,
        ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        CrewRepositoryInterface $crewRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->crewRaceRepository = $crewRaceRepository;
        $this->shipRumpCategoryRoleCrewRepository = $shipRumpCategoryRoleCrewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->crewRepository = $crewRepository;
        $this->userRepository = $userRepository;
    }

    public function create(int $userId, ?ColonyInterface $colony = null): ShipCrewInterface
    {
        $user = $this->userRepository->find($userId);

        $arr = [];
        $raceList = $this->crewRaceRepository->getByFaction((int)$user->getFactionId());
        foreach ($raceList as $obj) {
            $min = key($arr) + 1;
            $amount = range($min, $min + $obj->getChance());
            array_walk(
                $amount,
                function (&$value) use ($obj): void {
                    $value = $obj->getId();
                }
            );
            $arr = array_merge($arr, $amount);
        }
        $race = $this->crewRaceRepository->find((int)$arr[array_rand($arr)]);

        $gender = rand(1, 100) > $race->getMaleRatio() ? CrewEnum::CREW_GENDER_FEMALE : CrewEnum::CREW_GENDER_MALE;

        $crew = $this->crewRepository->prototype();

        $crew->setUser($user);
        $crew->setName('Crew');
        $crew->setRace($race);
        $crew->setGender($gender);
        $crew->setType(CrewEnum::CREW_TYPE_CREWMAN);
        $this->crewRepository->save($crew);

        $crewAssignment = $this->shipCrewRepository->prototype();
        $crewAssignment->setUser($user);
        $crewAssignment->setCrew($crew);
        if ($colony !== null) {
            $crewAssignment->setColony($colony);
            $colony->getCrewAssignments()->add($crewAssignment);
        }
        $this->shipCrewRepository->save($crewAssignment);

        return $crewAssignment;
    }

    public function createShipCrew(
        ShipInterface $ship,
        ?ColonyInterface $colony = null,
        ?ShipInterface $station = null
    ): void {
        $crewToSetup = $ship->getBuildPlan()->getCrew();

        foreach (CrewEnum::CREW_ORDER as $crewType) {
            $createdcount = 1;
            if ($crewType == CrewEnum::CREW_TYPE_CREWMAN) {
                $slot = 'getJob6Crew';
            } else {
                $slot = 'getJob' . $crewType . 'Crew';
            }
            $config = $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                $ship->getRump()->getShipRumpCategory()->getId(),
                $ship->getRump()->getShipRumpRole()->getId()
            );

            while ($crewToSetup > 0 && ($crewType == CrewEnum::CREW_TYPE_CREWMAN || $createdcount <= $config->$slot())) {
                $createdcount++;
                $crewToSetup--;

                $crewAssignment = $this->getCrewByType($crewType, $colony, $station);
                if ($crewAssignment === null) {
                    $crewAssignment = $this->getCrew($colony, $station);
                }
                $crewAssignment->setShip($ship);
                $crewAssignment->setColony(null);
                $crewAssignment->setTradepost(null);
                //TODO set both ship and crew user
                $crewAssignment->setUser($ship->getUser());
                $crewAssignment->setSlot($crewType);

                $ship->getCrewlist()->add($crewAssignment);

                $this->shipCrewRepository->save($crewAssignment);
            }
        }
    }

    private function getCrewByType(
        int $crewType,
        ?ColonyInterface $colony,
        ?ShipInterface $station
    ): ?ShipCrewInterface {
        if ($colony === null && $station === null) {
            throw new CrewOriginException('no origin available');
        }

        if ($colony !== null) {
            foreach ($colony->getCrewAssignments() as $crewAssignment) {
                $crew = $crewAssignment->getCrew();
                if ($crew->getType() === $crewType) {
                    $colony->getCrewAssignments()->removeElement($crewAssignment);

                    return $crewAssignment;
                }
            }

            return null;
        } else if ($station !== null) {
            foreach ($station->getCrewlist() as $crewAssignment) {
                $crew = $crewAssignment->getCrew();
                if ($crew->getType() === $crewType) {
                    $station->getCrewlist()->removeElement($crewAssignment);

                    return $crewAssignment;
                }
            }

            return null;
        }
    }

    private function getCrew(?ColonyInterface $colony, ?ShipInterface $station): ShipCrewInterface
    {
        if ($colony === null && $station === null) {
            throw new CrewOriginException('no origin available');
        }

        if ($colony !== null) {
            $crewAssignments = $colony->getCrewAssignments();
            /** @var ShipCrewInterface $random */
            $random = $crewAssignments->get(array_rand($crewAssignments->toArray()));

            //remove from colony
            $crewAssignments->removeElement($random);

            return $random;
        } else if ($station !== null) {
            $crewList = $station->getCrewlist();
            $random = $crewList->get(array_rand($crewList->toArray()));

            $crewList->removeElement($random);

            return $random;
        }
    }
}
