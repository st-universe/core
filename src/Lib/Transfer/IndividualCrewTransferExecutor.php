<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use JsonException;
use RuntimeException;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;

final class IndividualCrewTransferExecutor
{
    public function __construct(
        private IndividualCrewAssignmentLookup $assignmentLookup,
        private CrewAssignmentRepositoryInterface $assignmentRepository,
        private TroopTransferUtilityInterface $troopTransferUtility
    ) {}

    public function execute(
        string $placementsJson,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        IndividualCrewTransferSelection $selection,
        InformationInterface $information
    ): void {
        try {
            $placements = json_decode($placementsJson, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('validated crew placements could not be decoded', 0, $exception);
        }
        if (!is_array($placements)) {
            throw new RuntimeException('validated crew placements are not a list');
        }

        $user = $source->getUser();
        $assignments = [];
        $this->assignmentLookup->forEachOwnAssignment($source, $target, $user, static function (IndividualCrewAssignmentLocation $location) use (&$assignments): void {
            $assignments[$location->assignment->getCrew()->getId()] = $location;
        });
        $changes = [];
        foreach ($placements as $input) {
            $placement = IndividualCrewPlacement::fromInput($input);
            if ($placement === null) {
                throw new RuntimeException('validated crew placement is invalid');
            }
            if (!isset($assignments[$placement->id])) {
                throw new RuntimeException('validated crew assignment is missing');
            }
            $location = $assignments[$placement->id];
            if ($placement->side !== $location->side || $placement->slot !== $location->slot->value) {
                $changes[] = [$location->assignment, $location->side, $placement->side, CrewTypeEnum::from($placement->slot)];
            }
        }

        foreach ($changes as [$assignment, $originalSide, $side, $slot]) {
            $this->applyChange($assignment, $originalSide, $side, $slot, $source, $target);
        }

        $source->postCrewTransfer(0, $target, $information);
        $target->postCrewTransfer($selection->ownsTarget ? 0 : $selection->targetCount - $selection->originalTargetCount, $source, $information);
        $this->reportTransfers($selection, $source, $target, $information);
    }

    private function applyChange(
        CrewAssignment $assignment,
        int $originalSide,
        int $side,
        CrewTypeEnum $slot,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target
    ): void {
        $destination = ($side === 0 ? $source : $target)->get();
        if ($side !== $originalSide) {
            $this->troopTransferUtility->assignCrew($assignment, $destination, $destination instanceof Spacecraft ? $slot : null);
        } else {
            $assignment->setSlot($destination instanceof Spacecraft ? $slot : null);
            $this->assignmentRepository->save($assignment);
        }
    }

    private function reportTransfers(
        IndividualCrewTransferSelection $selection,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {
        if ($selection->arrivalsAtTarget > 0) {
            $information->addInformationf('%d Crew von %s zu %s transferiert', $selection->arrivalsAtTarget, $source->getName(), $target->getName());
        }
        if ($selection->arrivalsAtSource > 0) {
            $information->addInformationf('%d Crew von %s zu %s transferiert', $selection->arrivalsAtSource, $target->getName(), $source->getName());
        }
    }
}
