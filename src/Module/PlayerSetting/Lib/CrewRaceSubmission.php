<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Lib;

use request;
use Stu\Component\Crew\CrewRaceGraphics;
use Stu\Component\Crew\CrewRaceSubmissionNotifier;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\View\ShowCrewRaceManagement\ShowCrewRaceManagement;
use Stu\Orm\Entity\CrewRace;
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
        $crewRace = $this->getResubmittableCrewRace($game, $resubmitted, $user->getId());
        if ($resubmitted && $crewRace === null) {
            return;
        }
        if ($this->hasReachedSubmissionLimit($game, $resubmitted, $user->getId())) {
            return;
        }

        $submission = new CrewRaceSubmissionRequestFactory($this->crewRaceRepository)->create($game, $crewRace);
        if ($submission === null || !$this->crewRaceGraphics->store(
            $submission->gfxPath,
            $submission->maleRatio,
            $crewRace?->getGfxPath(),
            $game
        )) {
            return;
        }

        $crewRace ??= $this->crewRaceRepository->prototype();
        $crewRace
            ->setDescription($submission->description)
            ->setGfxPath($submission->gfxPath)
            ->setMaleRatio($submission->maleRatio)
            ->setChance($submission->chance)
            ->setCreatorUserId($user->getId())
            ->setShared($submission->shared)
            ->setCivil($submission->civil)
            ->setAccepted(false)
            ->setAcceptedUserId(null)
            ->setRejectionReason(null)
            ->setFactionIds($this->getFactionIds($user->getFactionId(), $submission->shared));

        $crewRace->incrementGraphicsVersion();

        $this->crewRaceRepository->save($crewRace);
        $this->submissionNotifier->notify($crewRace, $resubmitted);
        $game->getInfo()->addInformation($resubmitted
            ? _('Die Crew-Rasse wurde erneut zur Freigabe eingereicht')
            : _('Die Crew-Rasse wurde zur Freigabe eingereicht'));
    }

    private function getResubmittableCrewRace(
        GameControllerInterface $game,
        bool $resubmitted,
        int $userId
    ): ?CrewRace {
        if (!$resubmitted) {
            return null;
        }

        $crewRaceId = request::postInt('crew_race_id');
        $crewRace = $crewRaceId === 0 ? null : $this->crewRaceRepository->find($crewRaceId);
        if ($crewRace !== null && $crewRace->getCreatorUserId() === $userId && $crewRace->isRejected()) {
            return $crewRace;
        }

        $game->getInfo()->addInformation(_('Nur eigene abgelehnte Crew-Rassen können erneut eingereicht werden'));
        return null;
    }

    private function hasReachedSubmissionLimit(
        GameControllerInterface $game,
        bool $resubmitted,
        int $userId
    ): bool {
        if ($resubmitted || $game->isAdmin() || count($this->crewRaceRepository->getByCreatorUserId($userId)) < 3) {
            return false;
        }

        $game->getInfo()->addInformation(_('Du kannst maximal drei eigene Crew-Rassen erstellen'));
        return true;
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
            $factionId = (int) $factionId;
            if (in_array($factionId, $playableFactionIds, true)) {
                $factionIds[] = $factionId;
            }
        }

        return array_values(array_unique($factionIds));
    }
}
