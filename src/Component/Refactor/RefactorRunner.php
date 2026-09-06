<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository,
        private RelationRepositoryInterface $relationRepository,
        private RelationPermissionRepositoryInterface $relationPermissionRepository
    ) {}

    public function refactor(): void
    {
        foreach ($this->spacecraftRepository->findAll() as $spacecraft) {
            $this->restoreCrewPosts($spacecraft);
        }

        foreach ($this->relationRepository->getActiveByTypes([
            AllianceRelationTypeEnum::FRIENDS->value,
            AllianceRelationTypeEnum::ALLIED->value,
            AllianceRelationTypeEnum::VASSAL->value
        ]) as $relation) {
            $this->relationPermissionRepository->grantForRelation(
                $relation,
                RelationPermissionEnum::FRIENDLY
            );
        }
    }

    private function restoreCrewPosts(Spacecraft $spacecraft): void
    {
        $rump = $spacecraft->getRump();
        $rumpRole = $rump->getShipRumpRole();
        if ($rumpRole === null) {
            return;
        }

        $config = $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
            $rump->getShipRumpCategory()->getId(),
            $rumpRole->getId()
        );
        if ($config === null) {
            return;
        }

        $crewAssignments = $spacecraft->getCrewAssignments()->toArray();
        $availableCrew = [];
        foreach ($crewAssignments as $crewAssignment) {
            if (
                $crewAssignment->getCrew()->getUserId() === $spacecraft->getUser()->getId()
                && $crewAssignment->getSlot() === CrewTypeEnum::CREWMAN
            ) {
                $availableCrew[$crewAssignment->getCrew()->getId()] = $crewAssignment;
            }
        }

        foreach (CrewTypeEnum::getOrder() as $position) {
            if ($position === CrewTypeEnum::CREWMAN || $availableCrew === []) {
                continue;
            }

            $assignedCrewCount = count(array_filter(
                $crewAssignments,
                static fn(CrewAssignment $crewAssignment): bool => (
                    $crewAssignment->getCrew()->getUserId() === $spacecraft->getUser()->getId()
                    && $crewAssignment->getSlot() === $position
                )
            ));
            $freeSlots = $config->getCrewForPosition($position) - $assignedCrewCount;
            if ($freeSlots < 1) {
                continue;
            }

            uasort(
                $availableCrew,
                static fn(CrewAssignment $a, CrewAssignment $b): int => (
                    ($b->getCrew()->getSkillAt($position)?->getExpertise() ?? 0)
                    <=> ($a->getCrew()->getSkillAt($position)?->getExpertise() ?? 0)
                    ?: $a->getCrew()->getId() <=> $b->getCrew()->getId()
                )
            );

            foreach (array_slice($availableCrew, 0, $freeSlots, true) as $crewId => $crewAssignment) {
                $crewAssignment->setSlot($position);
                $this->crewAssignmentRepository->save($crewAssignment);
                unset($availableCrew[$crewId]);
            }
        }
    }
}
