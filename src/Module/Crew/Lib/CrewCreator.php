<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use CrewData;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use UserData;

final class CrewCreator implements CrewCreatorInterface
{
    private $crewRaceRepository;

    public function __construct(
        CrewRaceRepositoryInterface $crewRaceRepository
    ) {
        $this->crewRaceRepository = $crewRaceRepository;
    }

    public function create(int $userId): CrewData
    {
        /**
         * @var UserData $user
         */
        $user = ResourceCache()->getUser($userId);

        $arr = [];
        $raceList = $this->crewRaceRepository->getByFaction((int) $user->getFaction());
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
        $race = $this->crewRaceRepository->find((int) $arr[array_rand($arr)]);

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
}