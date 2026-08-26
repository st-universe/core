<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\UpdateCrewRaceDistribution;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\View\ShowCrewRaceManagement\ShowCrewRaceManagement;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class UpdateCrewRaceDistribution implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UPDATE_CREW_RACE_DISTRIBUTION';

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly FactionRepositoryInterface $factionRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewRaceManagement::VIEW_IDENTIFIER);

        $crewRace = $this->crewRaceRepository->find(request::postInt('crew_race_id'));
        if ($crewRace === null || $crewRace->getCreatorUserId() !== $game->getUser()->getId() || !$crewRace->canEditDistribution()) {
            $game->getInfo()->addInformation(_('Diese Crew-Rasse kann nicht geändert werden'));
            return;
        }

        $shared = $crewRace->isShared() || request::postString('crew_race_shared') === '1';
        $factionIds = $crewRace->getFactionIds();
        if ($shared) {
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
        }

        $crewRace
            ->setShared($shared)
            ->setFactionIds(array_values(array_unique($factionIds)));
        $this->crewRaceRepository->save($crewRace);
        $game->getInfo()->addInformation(_('Die Freigaben der Crew-Rasse wurden aktualisiert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
