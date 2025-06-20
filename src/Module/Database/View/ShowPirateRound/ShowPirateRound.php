<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowPirateRound;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\PirateRoundInterface;
use Stu\Orm\Entity\UserPirateRoundInterface;
use Stu\Orm\Repository\PirateRoundRepositoryInterface;
use Stu\Orm\Repository\UserPirateRoundRepositoryInterface;

final class ShowPirateRound implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_PIRATE_ROUND';

    private const array FACTION_COLORS = [
        1 => '#000099',
        2 => '#009900',
        3 => '#990000',
        4 => '#999900',
        5 => '#DF7401',
    ];

    public function __construct(
        private PirateRoundRepositoryInterface $pirateRoundRepository,
        private UserPirateRoundRepositoryInterface $userPirateRoundRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'database.php',
            'Datenbank'
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                self::VIEW_IDENTIFIER
            ),
            'Piraten-Runde'
        );
        $game->setPageTitle('/ Datenbank / Piraten-Runde');
        $game->setViewTemplate('html/database/pirateRound.twig');

        $lastRound = $this->getLastPirateRound();

        if ($lastRound === null) {
            $game->addInformation('Keine Piraten-Runde gefunden');
            return;
        }

        $userPirateRounds = $this->userPirateRoundRepository->findByPirateRound($lastRound->getId());
        $topTenUsers = array_slice($userPirateRounds, 0, 10);

        $topList = array_map(
            fn($userPirateRound) => [
                'user' => $userPirateRound->getUser(),
                'prestige' => $userPirateRound->getPrestige(),
                'destroyed_ships' => $userPirateRound->getDestroyedShips()
            ],
            $topTenUsers
        );

        $currentUser = $game->getUser();
        $userRoundData = $this->userPirateRoundRepository->findByUserAndPirateRound(
            $currentUser->getId(),
            $lastRound->getId()
        );

        $factionData = $this->getFactionPrestigeData($userPirateRounds);
        $totalDestroyedShips = array_sum(array_map(fn($userRound) => $userRound->getDestroyedShips(), $userPirateRounds));

        $winnerFactionShips = 0;
        if ($lastRound->getFactionWinner()) {
            foreach ($factionData as $faction) {
                if ($faction['id'] === $lastRound->getFactionWinner()) {
                    $winnerFactionShips = $faction['ships'];
                    break;
                }
            }
        }
        $maxPrestige = $lastRound->getMaxPrestige();
        $actualPrestige = $lastRound->getActualPrestige();
        $remainingPrestige = max(0, $maxPrestige - $actualPrestige);

        $game->setTemplateVar('REMAINING_PRESTIGE', $remainingPrestige);
        $game->setTemplateVar('WINNER_FACTION_SHIPS', $winnerFactionShips);
        $game->setTemplateVar('PIRATE_ROUND', $lastRound);
        $game->setTemplateVar('TOP_USERS', $topList);
        $game->setTemplateVar('TOTAL_DESTROYED_SHIPS', $totalDestroyedShips);
        $game->setTemplateVar('USER_ROUND_DATA', $userRoundData);
        $game->setTemplateVar('FACTION_DATA', $factionData);
    }

    private function getLastPirateRound(): ?PirateRoundInterface
    {
        $allRounds = $this->pirateRoundRepository->findBy([], ['id' => 'DESC'], 1);
        return empty($allRounds) ? null : $allRounds[0];
    }

    /**
     * @param UserPirateRoundInterface[] $userPirateRounds
     * @return array<int, array<string, mixed>>
     */
    private function getFactionPrestigeData(array $userPirateRounds): array
    {
        $factionPrestige = [];
        $factionShips = [];
        $factionData = [];

        foreach ($userPirateRounds as $userPirateRound) {
            $user = $userPirateRound->getUser();
            $faction = $user->getFaction();
            $factionId = $faction->getId();
            $prestige = $userPirateRound->getPrestige();
            $ships = $userPirateRound->getDestroyedShips();

            if (!isset($factionPrestige[$factionId])) {
                $factionPrestige[$factionId] = 0;
                $factionShips[$factionId] = 0;
                $factionData[$factionId] = [
                    'id' => $factionId,
                    'name' => $faction->getName(),
                    'color' => self::FACTION_COLORS[$factionId] ?? '#666666',
                ];
            }

            $factionPrestige[$factionId] += $prestige;
            $factionShips[$factionId] += $ships;
        }

        $totalPrestige = array_sum($factionPrestige);
        arsort($factionPrestige);

        $result = [];
        $rank = 1;

        foreach ($factionPrestige as $factionId => $prestige) {
            $percentage = $totalPrestige > 0 ? ($prestige / $totalPrestige) * 100 : 0;

            $result[] = [
                'id' => $factionId,
                'name' => $factionData[$factionId]['name'],
                'color' => $factionData[$factionId]['color'],
                'prestige' => $prestige,
                'ships' => $factionShips[$factionId],
                'percentage' => $percentage,
                'rank' => $rank++
            ];
        }

        return $result;
    }
}