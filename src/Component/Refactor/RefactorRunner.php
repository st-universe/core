<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ShipRumpCategoryRoleCrewRepositoryInterface $positionRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository
    ) {}

    public function refactor(): void
    {
        foreach ($this->spacecraftRepository->findAll() as $spacecraft) {
            $this->refactorSpacecraft($spacecraft);
        }
    }

    private function refactorSpacecraft(Spacecraft $spacecraft): void
    {
        $crewBySlot = [];
        foreach ($spacecraft->getCrewAssignments() as $crewAssignment) {
            $slot = $crewAssignment->getSlot();
            if ($slot === null || $slot === CrewTypeEnum::CREWMAN) {
                continue;
            }
            $crewBySlot[$slot->value][] = $crewAssignment;
        }

        if ($crewBySlot === []) {
            return;
        }

        $rump = $spacecraft->getRump();
        $role = $rump->getShipRumpRole();
        $config = $role === null
            ? null
            : $this->positionRepository->getByShipRumpCategoryAndRole(
                $rump->getShipRumpCategory()->getId(),
                $role->getId()
            );
        $ownerId = $spacecraft->getUser()->getId();

        foreach ($crewBySlot as $slotValue => $crewAssignments) {
            $slot = CrewTypeEnum::from($slotValue);
            $maximum = max(0, $config?->getCrewForPosition($slot) ?? 0);
            $excess = count($crewAssignments) - $maximum;
            if ($excess <= 0) {
                continue;
            }

            usort(
                $crewAssignments,
                static function (CrewAssignment $left, CrewAssignment $right) use ($ownerId, $slot): int {
                    $leftCrew = $left->getCrew();
                    $rightCrew = $right->getCrew();
                    $leftIsGuest = $leftCrew->getUser()->getId() !== $ownerId;
                    $rightIsGuest = $rightCrew->getUser()->getId() !== $ownerId;

                    return $rightIsGuest <=> $leftIsGuest
                        ?: ($leftCrew->getSkillAt($slot)?->getExpertise() ?? 0)
                        <=> ($rightCrew->getSkillAt($slot)?->getExpertise() ?? 0)
                        ?: $leftCrew->getId() <=> $rightCrew->getId();
                }
            );

            foreach (array_slice($crewAssignments, 0, $excess) as $crewAssignment) {
                $crewAssignment->setSlot(CrewTypeEnum::CREWMAN);
                $this->crewAssignmentRepository->save($crewAssignment);
            }
        }
    }
}
