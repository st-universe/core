<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Crew\CrewEnum;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\CrewRaceInterface;
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

    private EntityManagerInterface $entityManager;

    public function __construct(
        CrewRaceRepositoryInterface $crewRaceRepository,
        ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository,
        CrewRepositoryInterface $crewRepository,
        UserRepositoryInterface $userRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->crewRaceRepository = $crewRaceRepository;
        $this->shipRumpCategoryRoleCrewRepository = $shipRumpCategoryRoleCrewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->crewRepository = $crewRepository;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    public function create(int $userId): CrewInterface
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
        /** @var CrewRaceInterface $race */
        $race = $this->crewRaceRepository->find((int)$arr[array_rand($arr)]);

        $gender = rand(1, 100) > $race->getMaleRatio() ? CrewEnum::CREW_GENDER_FEMALE : CrewEnum::CREW_GENDER_MALE;

        $crew = $this->crewRepository->prototype();

        $crew->setUser($user);
        $crew->setName('Crew');
        $crew->setRace($race);
        $crew->setGender($gender);
        $crew->setType(CrewEnum::CREW_TYPE_CREWMAN);

        $this->crewRepository->save($crew);

        return $crew;
    }

    public function createShipCrew(ShipInterface $ship): void
    {
        $userId = $ship->getUser()->getId();

        $crewToSetup = $ship->getBuildPlan()->getCrew();

        foreach (CrewEnum::CREW_ORDER as $crewtype) {
            $createdcount = 1;
            if ($crewtype == CrewEnum::CREW_TYPE_CREWMAN) {
                $slot = 'getJob6Crew';
            } else {
                $slot = 'getJob' . $crewtype . 'Crew';
            }
            $config = $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                $ship->getRump()->getShipRumpCategory()->getId(),
                $ship->getRump()->getShipRumpRole()->getId()
            );

            while ($crewToSetup > 0 && ($crewtype == CrewEnum::CREW_TYPE_CREWMAN || $createdcount <= $config->$slot())) {
                $createdcount++;
                $crewToSetup--;

                $crew = current($this->crewRepository->getFreeByUserAndType($userId, $crewtype, 1));
                if (!$crew) {
                    $crew = current($this->crewRepository->getFreeByUser($userId, 1));
                }
                $sc = $this->shipCrewRepository->prototype();
                $sc->setCrew($crew);
                $sc->setShip($ship);
                $sc->setUser($ship->getUser());
                $sc->setSlot($crewtype);

                $ship->getCrewlist()->add($sc);

                $this->shipCrewRepository->save($sc);
                $this->entityManager->flush();
            }
        }
    }
}
