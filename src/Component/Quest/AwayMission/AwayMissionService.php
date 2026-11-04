<?php

declare(strict_types=1);

namespace Stu\Component\Quest\AwayMission;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\AwayMissionDialogue;
use Stu\Orm\Entity\AwayMissionField;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class AwayMissionService
{
    private const array REQUIRED_POSITIONS = [
        CrewTypeEnum::CAPTAIN,
        CrewTypeEnum::COMMAND,
        CrewTypeEnum::TACTIC,
        CrewTypeEnum::SCIENCE,
        CrewTypeEnum::TECHNICAL,
        CrewTypeEnum::NAVIGATION
    ];

    public function __construct(
        private AwayMissionRepositoryInterface $awayMissionRepository,
        private UserCrewRankRepositoryInterface $rankRepository,
        private AwayMissionChoices $choices
    ) {}

    public function isAvailable(AwayMission $mission): bool
    {
        $quest = $mission->getQuest();
        return $quest->getEnd() === null && $quest->getStart() <= time();
    }

    /** @return list<int> */
    public function getFieldIds(AwayMission $mission): array
    {
        return array_map(
            static fn (AwayMissionField $field): int => $field->getFieldId(),
            $this->awayMissionRepository->getFields($mission)
        );
    }

    public function getScannedFieldCount(AwayMission $mission, AwayMissionProgress $progress): int
    {
        return count(array_intersect($this->getFieldIds($mission), $progress->getScannedFields()));
    }

    public function isScanComplete(AwayMission $mission, AwayMissionProgress $progress): bool
    {
        $fields = $this->getFieldIds($mission);

        return $fields !== [] && array_diff($fields, $progress->getScannedFields()) === [];
    }

    /** @return array<int, CrewAssignment> */
    public function getCrew(Spacecraft $ship): array
    {
        $crew = [];
        foreach ($ship->getCrewAssignments() as $assignment) {
            $slot = $assignment->getSlot();
            if ($slot !== null) {
                $crew[$slot->value] ??= $assignment;
            }
        }

        return $crew;
    }

    /** @return list<string> */
    private function getMissingCrew(Spacecraft $ship): array
    {
        $crew = $this->getCrew($ship);
        $missing = [];
        foreach (self::REQUIRED_POSITIONS as $position) {
            if (!isset($crew[$position->value])) {
                $missing[] = $position->getDescription();
            }
        }
        return $missing;
    }

    public function hasRequiredCrew(Spacecraft $ship): bool
    {
        return $this->getMissingCrew($ship) === [];
    }

    public function checkCrew(Spacecraft $ship, InformationInterface $information): bool
    {
        $missing = $this->getMissingCrew($ship);
        if ($missing !== []) {
            $information->addInformation('Für die Außenmission fehlen: ' . implode(', ', $missing));
            return false;
        }

        return true;
    }

    /** @return list<list<AwayMissionDialogue>> */
    public function getPages(AwayMission $mission, int $factionId): array
    {
        $pages = [];
        foreach ($this->awayMissionRepository->getDialogue($mission, $factionId) as $entries) {
            foreach (array_chunk($entries, 4) as $page) {
                $pages[] = $page;
            }
        }

        return $pages;
    }

    public function getScanPage(AwayMission $mission, int $factionId): ?int
    {
        foreach ($this->getPages($mission, $factionId) as $index => $entries) {
            foreach ($entries as $entry) {
                if ($entry->isShowSurface()) {
                    return $index + 1;
                }
            }
        }

        return null;
    }

    public function canVisitPage(AwayMission $mission, AwayMissionProgress $progress, int $page): bool
    {
        $scanPage = $this->getScanPage($mission, $progress->getUser()->getFactionId());

        return $scanPage === null || $page <= $scanPage || $this->isScanComplete($mission, $progress);
    }

    public function prepareProgress(AwayMissionProgress $progress, Spacecraft $ship): void
    {
        $this->choices->initialize($progress, $ship);
        if (!$progress->isStarted()) {
            $progress->setStarted(true)->setLastPage(1);
        }
    }

    /** @return list<array{text: string, speaker: ?string, image: ?string, right: bool}> */
    public function renderPage(AwayMission $mission, Spacecraft $ship, int $page, AwayMissionProgress $progress): array
    {
        $pages = $this->getPages($mission, $ship->getUser()->getFactionId());
        $crew = $this->getCrew($ship);
        $result = [];
        $speechIndex = 0;
        foreach ($pages[$page - 1] ?? [] as $entry) {
            $speaker = $entry->getSpeaker();
            $assignment = $speaker === CrewTypeEnum::CREWMAN
                ? $this->choices->getReportingCrew($progress, $ship)
                : ($speaker === null ? null : ($crew[$speaker->value] ?? null));
            $text = $entry->getText();
            if ($speaker === CrewTypeEnum::CREWMAN && $assignment === null) {
                $text = 'Über die Verbindung zur {ship.name} ist eine Stimme zu hören: „' . $text . '“';
            }
            $result[] = [
                'text' => $this->replaceVariables($text, $ship, $progress),
                'speaker' => $assignment === null ? null : $this->getCrewLabel($assignment),
                'image' => $assignment === null ? null : $assignment->getCrew()->getRace()->getImagePath(
                    $assignment->getCrew()->getGenderShort(),
                    $assignment->getCrewImageType()
                ),
                'right' => $assignment !== null && $speechIndex++ % 2 === 1
            ];
        }

        return $result;
    }

    public function replaceVariables(string $text, Spacecraft $ship, ?AwayMissionProgress $progress = null): string
    {
        $variables = ['{ship.name}' => $ship->getName()];
        if ($progress !== null) {
            $colony = $progress->getMission()->getColony();
            $variables += [
                '{colony.name}' => $colony->getPlanetName() ?: ($colony->getName() ?: $colony->getSystem()->getName()),
                '{research.system}' => $progress->getResearchSystem()?->getDescription() ?? '',
                '{research.improved_system}' => $progress->getResearchSystem()?->getImprovedSystem() ?? '',
                '{research.finding}' => $progress->getResearchSystem()?->getFinding() ?? '',
                '{contact.signature}' => $this->choices->getContactSignature($progress)
            ];
        }
        foreach ($this->getCrew($ship) as $slot => $assignment) {
            $crew = $assignment->getCrew();
            $variables['{crew.' . $slot . '.name}'] = $crew->getName();
            $variables['{crew.' . $slot . '.rank}'] = $this->rankRepository->getRankName($crew->getUser(), $crew->getRank());
        }

        return strtr($text, $variables);
    }

    private function getCrewLabel(CrewAssignment $assignment): string
    {
        $crew = $assignment->getCrew();

        return $this->rankRepository->getRankName($crew->getUser(), $crew->getRank()) . ' ' . $crew->getName();
    }
}
