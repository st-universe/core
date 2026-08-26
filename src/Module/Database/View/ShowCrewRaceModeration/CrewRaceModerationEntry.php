<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCrewRaceModeration;

use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\User;

final class CrewRaceModerationEntry
{
    /** @param array<int, string> $factionNames */
    public function __construct(
        private readonly CrewRace $crewRace,
        private readonly ?User $creator,
        private readonly array $factionNames
    ) {}

    public function getCrewRace(): CrewRace
    {
        return $this->crewRace;
    }

    public function getCreatorName(): string
    {
        return $this->creator?->getName() ?? sprintf('Gelöschter Spieler (ID %d)', $this->crewRace->getCreatorUserId());
    }

    public function getFactionNames(): string
    {
        return implode(', ', array_filter(array_map(
            fn (int $factionId): ?string => $this->factionNames[$factionId] ?? null,
            $this->crewRace->getFactionIds()
        )));
    }
}
