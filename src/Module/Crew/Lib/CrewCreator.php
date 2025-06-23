<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use Override;
use RuntimeException;
use Stu\Component\Crew\CrewGenderEnum;
use Stu\Component\Crew\CrewOriginException;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

final class CrewCreator implements CrewCreatorInterface
{
    public function __construct(
        private CrewRaceRepositoryInterface $crewRaceRepository,
        private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private CrewRepositoryInterface $crewRepository
    ) {}

    #[Override]
    public function create(UserInterface $user, ?ColonyInterface $colony = null): CrewAssignmentInterface
    {
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

        $gender = random_int(1, 100) > $race->getMaleRatio() ? CrewGenderEnum::FEMALE : CrewGenderEnum::MALE;

        $crew = $this->crewRepository->prototype();

        $crew->setUser($user);
        $crew->setName('Crew');
        $crew->setRace($race);
        $crew->setGender($gender);
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
    public function createCrewAssignments(
        SpacecraftInterface $spacecraft,
        ColonyInterface|SpacecraftInterface $crewProvider,
        ?int $amount = null
    ): void {
        $crewToSetup = $amount ?? $spacecraft->getBuildPlan()->getCrew();

        foreach (CrewPositionEnum::getOrder() as $position) {
            $createdcount = 1;
            $slot = $position === CrewPositionEnum::CREWMAN ? 'getJob6Crew' : 'getJob' . $position->value . 'Crew';
            $config = $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                $spacecraft->getRump()->getShipRumpCategory()->getId(),
                $spacecraft->getRump()->getShipRumpRole()->getId()
            );
            if ($config === null) {
                throw new RuntimeException(sprintf(
                    'no rump category role crew for rumpCategoryId: %d, rumpRoleId: %d',
                    $spacecraft->getRump()->getShipRumpCategory()->getId()->value,
                    $spacecraft->getRump()->getShipRumpRole()->getId()->value
                ));
            }

            while ($crewToSetup > 0 && ($position === CrewPositionEnum::CREWMAN || $createdcount <= $config->$slot())) {
                $createdcount++;
                $crewToSetup--;

                $crewAssignment = $this->getCrewByType($position, $crewProvider);
                if ($crewAssignment === null) {
                    $crewAssignment = $this->getCrew($crewProvider);
                }

                if ($crewAssignment === null) {
                    throw new CrewOriginException('no assignable crew found');
                }

                $crewAssignment->setSpacecraft($spacecraft);
                $crewAssignment->setColony(null);
                $crewAssignment->setTradepost(null);
                $crewAssignment->setUser($spacecraft->getUser());
                $crewAssignment->setPosition($position);

                $spacecraft->getCrewAssignments()->add($crewAssignment);

                $this->shipCrewRepository->save($crewAssignment);
            }
        }
    }

    private function getCrewByType(
        CrewPositionEnum $position,
        ColonyInterface|SpacecraftInterface $crewProvider
    ): ?CrewAssignmentInterface {

        $filteredCrewAssignments = $crewProvider
            ->getCrewAssignments()
            ->filter(fn(CrewAssignmentInterface $assignment): bool => $assignment->getCrew()->isSkilledAt($position))
            ->toArray();

        if ($filteredCrewAssignments === []) {
            return null;
        }

        usort(
            $filteredCrewAssignments,
            fn(CrewAssignmentInterface $a, CrewAssignmentInterface $b): int =>
            $b->getCrew()->getSkills()->get($position->value)->getExpertise() - $a->getCrew()->getSkills()->get($position->value)->getExpertise()
        );


        $crewAssignment = current($filteredCrewAssignments);
        $crewProvider->getCrewAssignments()->removeElement($crewAssignment);

        return $crewAssignment;
    }

    private function getCrew(ColonyInterface|SpacecraftInterface $crewProvider): ?CrewAssignmentInterface
    {
        $crewAssignments = $crewProvider->getCrewAssignments();
        if ($crewAssignments->isEmpty()) {
            return null;
        }

        /** @var CrewAssignmentInterface $random */
        $random = $crewAssignments->get(array_rand($crewAssignments->toArray()));

        $crewAssignments->removeElement($random);

        return $random;
    }
}
