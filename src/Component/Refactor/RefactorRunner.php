<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private SpacecraftCrewCalculatorInterface $spacecraftCrewCalculator
    ) {}

    public function refactor(): void
    {
        foreach ($this->spacecraftRepository->findAll() as $spacecraft) {
            $crewAssignments = $spacecraft->getCrewAssignments()->toArray();
            usort(
                $crewAssignments,
                static fn (CrewAssignment $a, CrewAssignment $b): int =>
                    (($b->getSlot() !== null) <=> ($a->getSlot() !== null))
                    ?: $a->getCrew()->getId() <=> $b->getCrew()->getId()
            );

            $maximumRegularCrew = $this->spacecraftCrewCalculator->getMaxCrewCountByRump(
                $spacecraft->getRump()
            );

            foreach ($crewAssignments as $index => $crewAssignment) {
                $slot = $index < $maximumRegularCrew ? CrewTypeEnum::CREWMAN : null;
                if ($crewAssignment->getSlot() === $slot) {
                    continue;
                }

                $crewAssignment->setSlot($slot);
                $this->crewAssignmentRepository->save($crewAssignment);
            }
        }
    }
}
