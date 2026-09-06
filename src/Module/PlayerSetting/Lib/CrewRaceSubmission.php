<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Lib;

use request;
use Stu\Component\Crew\CrewRaceGraphics;
use Stu\Component\Crew\CrewRaceInput;
use Stu\Component\Crew\CrewRaceSubmissionNotifier;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\View\ShowCrewRaceManagement\ShowCrewRaceManagement;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class CrewRaceSubmission
{
    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly FactionRepositoryInterface $factionRepository,
        private readonly CrewRaceGraphics $crewRaceGraphics,
        private readonly CrewRaceSubmissionNotifier $submissionNotifier
    ) {}

    public function submit(GameControllerInterface $game, bool $resubmitted): void
    {
        $game->setView(ShowCrewRaceManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $crewRaceId = $resubmitted ? request::postInt('crew_race_id') : 0;
        $crewRace = $crewRaceId === 0 ? null : $this->crewRaceRepository->find($crewRaceId);
        if ($resubmitted && ($crewRace === null || $crewRace->getCreatorUserId() !== $user->getId() || !$crewRace->isRejected())) {
            $game->getInfo()->addInformation(_('Nur eigene abgelehnte Crew-Rassen können erneut eingereicht werden'));
            return;
        }
        if (!$resubmitted && !$game->isAdmin() && count($this->crewRaceRepository->getByCreatorUserId($user->getId())) >= 3) {
            $game->getInfo()->addInformation(_('Du kannst maximal drei eigene Crew-Rassen erstellen'));
            return;
        }

        $description = trim((string)request::postString('crew_race_name'));
        if (!CrewRaceInput::isValidDescription($description)) {
            $game->getInfo()->addInformation(_('Der Name muss mit einem Großbuchstaben beginnen und darf nur Buchstaben, einzelne Leerzeichen sowie einzelne Apostrophe oder Backticks enthalten'));
            return;
        }
        $maleRatio = filter_var(request::postString('crew_race_male_ratio'), FILTER_VALIDATE_INT);
        if ($maleRatio === false || $maleRatio < 0 || $maleRatio > 100) {
            $game->getInfo()->addInformation(_('Das Männerverhältnis muss eine Zahl zwischen 0 und 100 sein'));
            return;
        }

        $gfxPath = CrewRaceInput::normalizeDefine(request::postString('crew_race_define') ?: $description);
        if (!CrewRaceInput::isValidDefine($gfxPath)) {
            $game->getInfo()->addInformation(_('Die Grafikdefinition darf nur Großbuchstaben und einzelne Unterstriche enthalten'));
            return;
        }
        $existing = $this->crewRaceRepository->getByGfxPath($gfxPath);
        if ($existing !== null && ($crewRace === null || $existing->getId() !== $crewRace->getId())) {
            $game->getInfo()->addInformation(_('Eine Crew-Rasse mit dieser Grafikdefinition existiert bereits'));
            return;
        }

        $chance = filter_var(request::postString('crew_race_chance'), FILTER_VALIDATE_INT);
        if ($chance === false || $chance < 1 || $chance > 100) {
            $game->getInfo()->addInformation(_('Die Zufallsrate muss eine Zahl zwischen 1 und 100 sein'));
            return;
        }

        $shared = request::postString('crew_race_shared') === '1';
        if (!$this->crewRaceGraphics->store($gfxPath, (int)$maleRatio, $crewRace?->getGfxPath(), $game)) {
            return;
        }

        $crewRace ??= $this->crewRaceRepository->prototype();
        $crewRace
            ->setDescription($description)
            ->setGfxPath($gfxPath)
            ->setMaleRatio((int)$maleRatio)
            ->setChance((int)$chance)
            ->setCreatorUserId($user->getId())
            ->setShared($shared)
            ->setCivil(request::postString('crew_race_civil') === '1')
            ->setAccepted(false)
            ->setAcceptedUserId(null)
            ->setRejectionReason(null)
            ->setFactionIds($this->getFactionIds($user->getFactionId(), $shared));

        $crewRace->incrementGraphicsVersion();

        $this->crewRaceRepository->save($crewRace);
        $this->submissionNotifier->notify($crewRace, $resubmitted);
        $game->getInfo()->addInformation($resubmitted
            ? _('Die Crew-Rasse wurde erneut zur Freigabe eingereicht')
            : _('Die Crew-Rasse wurde zur Freigabe eingereicht'));
    }

    /** @return list<int> */
    private function getFactionIds(int $ownFactionId, bool $shared): array
    {
        $factionIds = [$ownFactionId];
        if (!$shared) {
            return $factionIds;
        }

        $playableFactionIds = array_map(
            static fn (Faction $faction): int => $faction->getId(),
            $this->factionRepository->getByChooseable(true)
        );
        foreach (request::postArray('crew_race_factions') as $factionId) {
            $factionId = (int)$factionId;
            if (in_array($factionId, $playableFactionIds, true)) {
                $factionIds[] = $factionId;
            }
        }

        return array_values(array_unique($factionIds));
    }

}
