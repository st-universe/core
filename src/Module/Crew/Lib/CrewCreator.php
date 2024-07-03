<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use Override;
use RuntimeException;
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
    public function __construct(private CrewRaceRepositoryInterface $crewRaceRepository, private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository, private ShipCrewRepositoryInterface $shipCrewRepository, private CrewRepositoryInterface $crewRepository, private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function create(int $userId, ?ColonyInterface $colony = null): ShipCrewInterface
    {
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            throw new RuntimeException('user not found1');
        }

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
            $arr = [...$arr, ...$amount];
        }
        $race = $this->crewRaceRepository->find($arr[array_rand($arr)]);

        $gender = random_int(1, 100) > $race->getMaleRatio() ? CrewEnum::CREW_GENDER_FEMALE : CrewEnum::CREW_GENDER_MALE;

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

    #[Override]
    public function createShipCrew(
        ShipInterface $ship,
        ColonyInterface|ShipInterface $crewProvider,
        ?int $amount = null
    ): void {
        $crewToSetup = $amount ?? $ship->getBuildPlan()->getCrew();

        foreach (CrewEnum::CREW_ORDER as $crewType) {
            $createdcount = 1;
            $slot = $crewType == CrewEnum::CREW_TYPE_CREWMAN ? 'getJob6Crew' : 'getJob' . $crewType . 'Crew';
            $config = $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                $ship->getRump()->getShipRumpCategory()->getId(),
                $ship->getRump()->getShipRumpRole()->getId()
            );

            while ($crewToSetup > 0 && ($crewType == CrewEnum::CREW_TYPE_CREWMAN || $createdcount <= $config->$slot())) {
                $createdcount++;
                $crewToSetup--;

                $crewAssignment = $this->getCrewByType($crewType, $crewProvider);
                if ($crewAssignment === null) {
                    $crewAssignment = $this->getCrew($crewProvider);
                }

                if ($crewAssignment === null) {
                    throw new CrewOriginException('no assignable crew found');
                }

                $crewAssignment->setShip($ship);
                $crewAssignment->setColony(null);
                $crewAssignment->setTradepost(null);
                //TODO set both ship and crew user
                $crewAssignment->setUser($ship->getUser());
                $crewAssignment->setSlot($crewType);

                $ship->getCrewAssignments()->add($crewAssignment);

                $this->shipCrewRepository->save($crewAssignment);
            }
        }
    }

    private function getCrewByType(
        int $crewType,
        ColonyInterface|ShipInterface $crewProvider
    ): ?ShipCrewInterface {

        foreach ($crewProvider->getCrewAssignments() as $crewAssignment) {
            $crew = $crewAssignment->getCrew();
            if ($crew->getType() === $crewType) {
                $crewProvider->getCrewAssignments()->removeElement($crewAssignment);

                return $crewAssignment;
            }
        }

        return null;
    }

    private function getCrew(ColonyInterface|ShipInterface $crewProvider): ?ShipCrewInterface
    {
        $crewAssignments = $crewProvider->getCrewAssignments();

        if ($crewAssignments->isEmpty()) {
            return null;
        }

        /** @var ShipCrewInterface $random */
        $random = $crewAssignments->get(array_rand($crewAssignments->toArray()));

        $crewAssignments->removeElement($random);

        return $random;
    }
}
