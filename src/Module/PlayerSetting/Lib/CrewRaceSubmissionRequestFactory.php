<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Lib;

use request;
use Stu\Component\Crew\CrewRaceInput;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;

final class CrewRaceSubmissionRequestFactory
{
    public function __construct(private readonly CrewRaceRepositoryInterface $crewRaceRepository) {}

    public function create(GameControllerInterface $game, ?CrewRace $crewRace): ?CrewRaceSubmissionData
    {
        $description = $this->getDescription($game);
        if ($description === null) {
            return null;
        }

        $maleRatio = $this->getMaleRatio($game);
        if ($maleRatio === null) {
            return null;
        }

        $gfxPath = $this->getGfxPath($game, $crewRace, $description);
        if ($gfxPath === null) {
            return null;
        }

        $chance = $this->getChance($game);
        if ($chance === null) {
            return null;
        }

        return new CrewRaceSubmissionData(
            $description,
            $maleRatio,
            $gfxPath,
            $chance,
            request::postString('crew_race_shared') === '1',
            request::postString('crew_race_civil') === '1'
        );
    }

    private function getDescription(GameControllerInterface $game): ?string
    {
        $description = trim((string) request::postString('crew_race_name'));
        if (CrewRaceInput::isValidDescription($description)) {
            return $description;
        }

        $game->getInfo()->addInformation(_('Der Name muss mit einem Großbuchstaben beginnen und darf nur Buchstaben, einzelne Leerzeichen sowie einzelne Apostrophe oder Backticks enthalten'));
        return null;
    }

    private function getMaleRatio(GameControllerInterface $game): ?int
    {
        $maleRatio = filter_var(request::postString('crew_race_male_ratio'), FILTER_VALIDATE_INT);
        if ($maleRatio !== false && $maleRatio >= 0 && $maleRatio <= 100) {
            return $maleRatio;
        }

        $game->getInfo()->addInformation(_('Das Männerverhältnis muss eine Zahl zwischen 0 und 100 sein'));
        return null;
    }

    private function getGfxPath(
        GameControllerInterface $game,
        ?CrewRace $crewRace,
        string $description
    ): ?string {
        $gfxPath = CrewRaceInput::normalizeDefine(request::postString('crew_race_define') ?: $description);
        if (!CrewRaceInput::isValidDefine($gfxPath)) {
            $game->getInfo()->addInformation(_('Die Grafikdefinition darf nur Großbuchstaben und einzelne Unterstriche enthalten'));
            return null;
        }

        $existing = $this->crewRaceRepository->getByGfxPath($gfxPath);
        if ($existing === null || ($crewRace !== null && $existing->getId() === $crewRace->getId())) {
            return $gfxPath;
        }

        $game->getInfo()->addInformation(_('Eine Crew-Rasse mit dieser Grafikdefinition existiert bereits'));
        return null;
    }

    private function getChance(GameControllerInterface $game): ?int
    {
        $chance = filter_var(request::postString('crew_race_chance'), FILTER_VALIDATE_INT);
        if ($chance !== false && $chance >= 1 && $chance <= 100) {
            return $chance;
        }

        $game->getInfo()->addInformation(_('Die Zufallsrate muss eine Zahl zwischen 1 und 100 sein'));
        return null;
    }
}
