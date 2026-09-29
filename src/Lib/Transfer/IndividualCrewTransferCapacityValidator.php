<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use JsonException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Orm\Entity\User;

final class IndividualCrewTransferCapacityValidator
{
    public function __construct(private IndividualCrewTransferSideProvider $sideProvider) {}

    public function validate(
        string $placementsJson,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        IndividualCrewTransferSelection $selection,
        InformationInterface $information
    ): bool {
        $user = $source->getUser();
        $sides = [$this->sideProvider->getSide($source, $user, false), $this->sideProvider->getSide($target, $user, true)];
        $slotCounts = [[], []];
        foreach ($sides as $sideIndex => $side) {
            foreach ($side['positions'] as $slot => $position) {
                $slotCounts[$sideIndex][$slot] = $position['occupiedCount'] - count($position['crewAssignments']);
            }
        }

        try {
            $placements = json_decode($placementsJson, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $information->addInformation('Die Crewauswahl ist ungültig');
            return false;
        }
        if (!is_array($placements)) {
            $information->addInformation('Die Crewauswahl ist ungültig');
            return false;
        }
        foreach ($placements as $input) {
            $placement = IndividualCrewPlacement::fromInput($input);
            if ($placement === null) {
                $information->addInformation('Die Crewauswahl ist ungültig. Bitte öffne das Transferfenster erneut');
                return false;
            }
            $slotCounts[$placement->side][$placement->slot]++;
        }

        foreach ($sides as $sideIndex => $side) {
            $count = $sideIndex === 0 ? $selection->sourceCount : $selection->targetCount;
            if ($count < $side['minimum'] || $count > $side['maximum']) {
                $information->addInformation('Mindestcrew oder Crewkapazität würden verletzt. Bitte passe die Auswahl an');
                return false;
            }
            foreach ($side['positions'] as $slot => $position) {
                if ($position['capacity'] !== null
                    && $slotCounts[$sideIndex][$slot] > max($position['capacity'], $position['occupiedCount'])) {
                    $information->addInformation('Für den ausgewählten Crewposten ist kein Platz mehr frei');
                    return false;
                }
            }
        }

        if (!$selection->hasChanges) {
            $information->addInformation('Es wurden keine Änderungen ausgewählt');
            return false;
        }

        return $this->checkStorage($source, $selection->sourceCount - $sides[0]['count'], $selection->arrivalsAtSource, $user, $information)
            && $this->checkStorage($target, $selection->targetCount - $sides[1]['count'], $selection->arrivalsAtTarget, $user, $information);
    }

    private function checkStorage(
        StorageEntityWrapperInterface $wrapper,
        int $increase,
        int $arrivals,
        User $user,
        InformationInterface $information
    ): bool {
        return $wrapper->checkCrewStorage(abs($increase), $increase < 0, $information)
            && ($arrivals === 0 || $wrapper->acceptsCrewFrom(max(0, $increase), $user, $information));
    }
}
