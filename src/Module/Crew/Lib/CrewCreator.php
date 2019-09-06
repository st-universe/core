<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use Crew;
use CrewData;
use ShipData;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use UserData;

final class CrewCreator implements CrewCreatorInterface
{
    private $crewRaceRepository;

    private $shipRumpCategoryRoleCrewRepository;

    private $shipCrewRepository;

    public function __construct(
        CrewRaceRepositoryInterface $crewRaceRepository,
        ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository,
        ShipCrewRepositoryInterface $shipCrewRepository
    ) {
        $this->crewRaceRepository = $crewRaceRepository;
        $this->shipRumpCategoryRoleCrewRepository = $shipRumpCategoryRoleCrewRepository;
        $this->shipCrewRepository = $shipCrewRepository;
    }

    public function create(int $userId): CrewData
    {
        /**
         * @var UserData $user
         */
        $user = ResourceCache()->getUser($userId);

        $arr = [];
        $raceList = $this->crewRaceRepository->getByFaction((int)$user->getFaction());
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

        $gender = rand(1, 100) > $race->getMaleRatio() ? CREW_GENDER_FEMALE : CREW_GENDER_MALE;

        $crew = new CrewData();
        $crew->setUserId($userId);
        $crew->setName('Crew');
        $crew->setRaceId($race->getId());
        $crew->setGender($gender);
        $crew->setType(CREW_TYPE_CREWMAN);
        $crew->save();
        return $crew;
    }

    public function createShipCrew(ShipData $ship): void
    {
        for ($i = CREW_TYPE_FIRST; $i <= CREW_TYPE_LAST; $i++) {
            $j = 1;
            if ($i == CREW_TYPE_CREWMAN) {
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
                (int)$ship->getRump()->getCategegoryId(),
                (int)$ship->getRump()->getRoleId()
            );
            while ($j <= $config->$slot()) {
                $j++;
                if (($crew = Crew::getFreeCrewByTypeAndUser($ship->getUserId(), $i)) === false) {
                    $crew = Crew::getFreeCrewByTypeAndUser($ship->getUserId());
                }
                $sc = $this->shipCrewRepository->prototype();
                $sc->setCrewId((int) $crew->getId());
                $sc->setShipId((int) $ship->getId());
                $sc->setUserId((int) $ship->getUserId());
                $sc->setSlot($i);

                $this->shipCrewRepository->save($sc);
            }
        }
    }
}