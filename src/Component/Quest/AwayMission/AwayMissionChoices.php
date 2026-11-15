<?php

declare(strict_types=1);

namespace Stu\Component\Quest\AwayMission;

use RuntimeException;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class AwayMissionChoices
{
    public function __construct(private StuRandom $random, private FactionRepositoryInterface $factionRepository, private AwayMissionRepositoryInterface $missionRepository) {}

    public function initialize(AwayMissionProgress $progress, Spacecraft $ship): void
    {
        if ($progress->getResearchSystem() !== null && $progress->getContactFaction() !== null) {
            return;
        }

        if ($progress->getResearchSystem() === null) {
            $this->initializeResearchSystem($progress);
        }
        $factions = array_values(array_filter(
            $this->factionRepository->getByChooseable(true),
            static fn ($faction): bool => $faction->getId() !== $ship->getUser()->getFactionId()
        ));
        if ($factions === []) {
            throw new RuntimeException('Keine fremde spielbare Fraktion für die Außenmission verfügbar');
        }
        $progress->setContactFaction($progress->getContactFaction() ?? $factions[$this->random->rand(0, count($factions) - 1)]);

        $crewmen = $ship->getCrewAssignments()->filter(
            static fn (CrewAssignment $assignment): bool => in_array($assignment->getSlot(), [null, CrewTypeEnum::CREWMAN], true)
        )->getValues();
        if ($crewmen !== []) {
            $progress->setReportingCrewId($crewmen[$this->random->rand(0, count($crewmen) - 1)]->getCrew()->getId());
        }
    }

    private function initializeResearchSystem(AwayMissionProgress $progress): void
    {
        $mission = $progress->getMission();
        $this->missionRepository->lockMission($mission);
        $counts = $this->missionRepository->getResearchAssignmentCounts($mission);
        $systems = AwayMissionResearchEnum::cases();
        $minimum = min(array_map(static fn (AwayMissionResearchEnum $system): int => $counts[$system->value] ?? 0, $systems));
        $leastAssigned = array_values(array_filter($systems, static fn (AwayMissionResearchEnum $system): bool => ($counts[$system->value] ?? 0) === $minimum));
        $progress->setResearchSystem($leastAssigned[$this->random->rand(0, count($leastAssigned) - 1)]
            ?? throw new RuntimeException('Keine Forschungszuweisung für die Außenmission verfügbar'));
    }

    public function getReportingCrew(AwayMissionProgress $progress, Spacecraft $ship): ?CrewAssignment
    {
        foreach ($ship->getCrewAssignments() as $assignment) {
            if ($assignment->getCrew()->getId() === $progress->getReportingCrewId()
                && in_array($assignment->getSlot(), [null, CrewTypeEnum::CREWMAN], true)) {
                return $assignment;
            }
        }

        return null;
    }

    public function getContactSignature(AwayMissionProgress $progress): string
    {
        return match ($progress->getContactFaction()?->getId()) {
            1 => 'die Antriebssignatur eines Schiffes der Föderation',
            2 => 'die Antriebssignatur eines Schiffes des Romulanischen Imperiums',
            3 => 'die Antriebssignatur eines Schiffes des Klingonischen Imperiums',
            4 => 'die Antriebssignatur eines Schiffes der Cardassianischen Union',
            5 => 'die Antriebssignatur eines Schiffes der Ferengi-Allianz',
            default => 'die Antriebssignatur eines fremden Schiffes'
        };
    }
}
