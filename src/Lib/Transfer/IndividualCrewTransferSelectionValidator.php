<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use JsonException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;

final class IndividualCrewTransferSelectionValidator
{
    public function __construct(
        private IndividualCrewTransferSideProvider $sideProvider,
        private IndividualCrewAssignmentLookup $assignmentLookup
    ) {}

    public function validate(
        string $placementsJson,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): ?IndividualCrewTransferSelection {
        try {
            $placements = json_decode($placementsJson, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $information->addInformation('Die Crewauswahl ist ungültig');
            return null;
        }
        if (!is_array($placements) || !array_is_list($placements)) {
            $information->addInformation('Die Crewauswahl ist ungültig');
            return null;
        }

        $user = $source->getUser();
        $sides = [$this->sideProvider->getSide($source, $user, false), $this->sideProvider->getSide($target, $user, true)];
        $assignments = [];
        $this->assignmentLookup->forEachOwnAssignment($source, $target, $user, static function (IndividualCrewAssignmentLocation $location) use (&$assignments): void {
            $assignments[$location->assignment->getCrew()->getId()] = $location;
        });
        $seen = [];
        $counts = [$sides[0]['fixedCount'], $sides[1]['fixedCount']];
        $arrivals = [0, 0];
        $hasChanges = false;

        foreach ($placements as $input) {
            $placement = IndividualCrewPlacement::fromInput($input);
            if ($placement === null || !isset($assignments[$placement->id]) || isset($seen[$placement->id])
                || !isset($sides[$placement->side]['positions'][$placement->slot])) {
                $information->addInformation('Die Crewauswahl ist ungültig. Bitte öffne das Transferfenster erneut');
                return null;
            }

            $location = $assignments[$placement->id];
            if ($placement->originalSide !== $location->side || $placement->originalSlot !== $location->slot->value) {
                $information->addInformation('Die Crewzuordnung hat sich geändert. Bitte öffne das Transferfenster erneut');
                return null;
            }

            $seen[$placement->id] = true;
            $counts[$placement->side]++;
            $hasChanges = $hasChanges || $placement->side !== $location->side || $placement->slot !== $location->slot->value;
            if ($placement->side !== $location->side) {
                $arrivals[$placement->side]++;
            }
        }

        if (count($seen) !== count($assignments)) {
            $information->addInformation('Die Crewzusammensetzung hat sich geändert. Bitte öffne das Transferfenster erneut');
            return null;
        }
        return new IndividualCrewTransferSelection(
            $counts[0],
            $counts[1],
            $arrivals[0],
            $arrivals[1],
            $sides[1]['count'],
            $sides[1]['ownsEntity'],
            $hasChanges
        );
    }
}
