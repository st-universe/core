<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCrewRaceModeration;

use Stu\Module\Control\AccessCheckControllerInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\CrewRace;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowCrewRaceModeration implements ViewControllerInterface, AccessCheckControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREW_RACE_MODERATION';

    public function __construct(
        private readonly CrewRaceRepositoryInterface $crewRaceRepository,
        private readonly FactionRepositoryInterface $factionRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum
    {
        return AccessGrantedFeatureEnum::CREW_RACE_MODERATION;
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $factionNames = [];
        foreach ($this->factionRepository->getByChooseable(true) as $faction) {
            $factionNames[$faction->getId()] = $faction->getName();
        }

        $game->setViewTemplate('html/database/crewRaceModeration.twig');
        $game->setPageTitle(_('Crew Moderation'));
        $game->appendNavigationPart('database.php', _('Datenbank'));
        $game->appendNavigationPart('?SHOW_CREW_RACE_MODERATION=1', _('Crew Moderation'));
        $game->setTemplateVar('PENDING_CREW_RACES', $this->createEntries($this->crewRaceRepository->getPendingCustomRaces(), $factionNames));
        $game->setTemplateVar('ACCEPTED_CREW_RACES', $this->createEntries($this->crewRaceRepository->getAcceptedCustomRaces(), $factionNames));
    }

    /**
     * @param list<CrewRace> $crewRaces
     * @param array<int, string> $factionNames
     * @return list<CrewRaceModerationEntry>
     */
    private function createEntries(array $crewRaces, array $factionNames): array
    {
        return array_map(
            function (CrewRace $crewRace) use ($factionNames): CrewRaceModerationEntry {
                $creatorUserId = $crewRace->getCreatorUserId();

                return new CrewRaceModerationEntry(
                    $crewRace,
                    $creatorUserId === null ? null : $this->userRepository->find($creatorUserId),
                    $factionNames
                );
            },
            $crewRaces
        );
    }
}
