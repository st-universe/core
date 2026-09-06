<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\UserRelation;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRelationRepositoryInterface;

final class RefactorRunner
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private SpacecraftCrewCalculatorInterface $spacecraftCrewCalculator,
        private AllianceRelationRepositoryInterface $legacyAllianceRelationRepository,
        private UserRelationRepositoryInterface $legacyUserRelationRepository,
        private RelationRepositoryInterface $relationRepository
    ) {}

    public function refactor(): void
    {
        $this->migrateRelations();

        foreach ($this->spacecraftRepository->findAll() as $spacecraft) {
            $crewAssignments = $spacecraft->getCrewAssignments()->toArray();
            usort(
                $crewAssignments,
                static fn(CrewAssignment $a, CrewAssignment $b): int => (
                    ($b->getSlot() !== null) <=> ($a->getSlot() !== null)
                    ?: $a->getCrew()->getId() <=> $b->getCrew()->getId()
                )
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

    private function migrateRelations(): void
    {
        if ($this->relationRepository->findAll() !== []) {
            throw new \LogicException('Die Tabelle stu_relations muss vor dem Import leer sein');
        }

        foreach ($this->legacyAllianceRelationRepository->findAll() as $legacyRelation) {
            $this->copyAllianceRelation($legacyRelation);
        }

        foreach ($this->legacyUserRelationRepository->findAll() as $legacyRelation) {
            $this->copyUserRelation($legacyRelation);
        }
    }

    private function copyAllianceRelation(AllianceRelation $legacyRelation): void
    {
        $this->relationRepository->save(
            $this->relationRepository
                ->prototype()
                ->setSourceAlliance($legacyRelation->getAlliance())
                ->setRecipientAlliance($legacyRelation->getOpponent())
                ->setType($legacyRelation->getType())
                ->setDate($legacyRelation->getDate())
                ->setText($legacyRelation->getText())
                ->setLastEdited($legacyRelation->getLastEdited())
        );
    }

    private function copyUserRelation(UserRelation $legacyRelation): void
    {
        $this->relationRepository->save(
            $this->relationRepository
                ->prototype()
                ->setSourceUser($legacyRelation->getSourceUser())
                ->setSourceAlliance($legacyRelation->getSourceAlliance())
                ->setRecipientUser($legacyRelation->getRecipientUser())
                ->setRecipientAlliance($legacyRelation->getRecipientAlliance())
                ->setType($legacyRelation->getType())
                ->setDate($legacyRelation->getDate())
        );
    }
}
