<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

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
    private $crewRaceRepository;

    private $shipRumpCategoryRoleCrewRepository;

    private $shipCrewRepository;

    private $crewRepository;

    private $userRepository;

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

        for ($i = CrewEnum::CREW_TYPE_FIRST; $i <= CrewEnum::CREW_TYPE_LAST; $i++) {
            $j = 1;
            if ($i == CrewEnum::CREW_TYPE_CREWMAN) {
                $percentage = $ship->getBuildPlan()->getCrewPercentage();
                // @todo refactor
                switch ($percentage) {
                    case 100:
                        $slot = 'getJob6Crew';
                        break;
                    case 110:
                        $slot = 'getJob6Crew10p';
                        break;
                    case 120:
                        $slot = 'getJob6Crew20p';
                        break;
                }
            } else {
                $slot = 'getJob' . $i . 'Crew';
            }
            $config = $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                (int)$ship->getRump()->getShipRumpCategory()->getId(),
                (int)$ship->getRump()->getShipRumpRole()->getId()
            );
            while ($j <= $config->$slot()) {
                $j++;
                if (($crew = $this->crewRepository->getFreeByUserAndType($userId, $i)) === null) {
                    $crew = $this->crewRepository->getFreeByUser($userId);

                }
                $sc = $this->shipCrewRepository->prototype();
                $sc->setCrew($crew);
                $sc->setShip($ship);
                $sc->setUser($ship->getUser());
                $sc->setSlot($i);

                $this->shipCrewRepository->save($sc);
            }
        }
    }
}
