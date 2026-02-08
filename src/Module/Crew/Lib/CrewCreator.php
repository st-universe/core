<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use InvalidArgumentException;
use Stu\Component\Crew\CrewOriginException;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CrewCreator implements CrewCreatorInterface
{
    public function __construct(
        private CrewRaceRepositoryInterface $crewRaceRepository,
        private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private CrewRepositoryInterface $crewRepository,
        private UserRepositoryInterface $userRepository,
        private StuRandom $stuRandom
    ) {}

    #[\Override]
    public function create(int $userId, ?Colony $colony = null): CrewAssignment
    {
        $user = $this->userRepository->find($userId);

        if ($user === null) {
            throw new InvalidArgumentException('user not found1');
        }

        $arr = [];
        $raceList = $this->crewRaceRepository->getByFaction($user->getFactionId());
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
        $randomRaceId = $arr[$this->stuRandom->array_rand($arr)];
        $race = $this->crewRaceRepository->find($randomRaceId);
        if ($race === null) {
            throw new SanityCheckException(sprintf('raceId %d does not exist', $randomRaceId));
        }

        $gender = random_int(1, 100) > $race->getMaleRatio() ? Crew::CREW_GENDER_FEMALE : Crew::CREW_GENDER_MALE;

        $crew = $this->crewRepository->prototype();

        $crew->setUser($user);
        $crew->setName('Crew');
        $crew->setRace($race);
        $crew->setGender($gender);
        $crew->setType(CrewTypeEnum::CREWMAN);
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

    #[\Override]
    public function createCrewAssignments(
        Spacecraft $spacecraft,
        EntityWithCrewAssignmentsInterface $crewProvider,
        ?int $amount = null
    ): void {
        $crewToSetup = $amount ?? $spacecraft->getBuildPlan()?->getCrew() ?? 0;
        $shipRumpRole = $spacecraft->getRump()->getShipRumpRole();
        if ($shipRumpRole === null) {
            throw new SanityCheckException(sprintf('rumpId %d does not have rump role', $spacecraft->getRump()->getId()));
        }

        foreach (CrewTypeEnum::getOrder() as $crewType) {
            $createdcount = 1;
            $config = $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                $spacecraft->getRump()->getShipRumpCategory()->getId(),
                $shipRumpRole->getId()
            );
            if ($config === null) {
                throw new InvalidArgumentException(sprintf(
                    'no rump category role crew for rumpCategoryId: %d, rumpRoleId: %d',
                    $spacecraft->getRump()->getShipRumpCategory()->getId()->value,
                    $shipRumpRole->getId()->value
                ));
            }

            while ($crewToSetup > 0 && ($crewType == CrewTypeEnum::CREWMAN || $createdcount <= $config->getCrewForPosition($crewType))) {
                $createdcount++;
                $crewToSetup--;

                $crewAssignment = $this->getCrewByType($crewType, $crewProvider);
                if ($crewAssignment === null) {
                    $crewAssignment = $this->getCrew($crewProvider);
                }

                if ($crewAssignment === null) {
                    throw new CrewOriginException('no assignable crew found');
                }

                $crewAssignment->setSpacecraft($spacecraft);
                $crewAssignment->setColony(null);
                $crewAssignment->setTradepost(null);
                //TODO set both ship and crew user
                $crewAssignment->setUser($spacecraft->getUser());
                $crewAssignment->setSlot($crewType);

                $spacecraft->getCrewAssignments()->add($crewAssignment);

                $this->shipCrewRepository->save($crewAssignment);
            }
        }
    }

    private function getCrewByType(
        CrewTypeEnum $crewType,
        EntityWithCrewAssignmentsInterface $crewProvider
    ): ?CrewAssignment {

        foreach ($crewProvider->getCrewAssignments() as $crewAssignment) {
            $crew = $crewAssignment->getCrew();
            if ($crew->getType() === $crewType) {
                $crewProvider->getCrewAssignments()->removeElement($crewAssignment);

                return $crewAssignment;
            }
        }

        return null;
    }

    private function getCrew(EntityWithCrewAssignmentsInterface $crewProvider): ?CrewAssignment
    {
        $crewAssignments = $crewProvider->getCrewAssignments();

        if ($crewAssignments->isEmpty()) {
            return null;
        }

        /** @var CrewAssignment $random */
        $random = $crewAssignments->get($this->stuRandom->array_rand($crewAssignments->toArray()));

        $crewAssignments->removeElement($random);

        return $random;
    }
}
