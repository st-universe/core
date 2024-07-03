<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ColonyWorthRanking;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopListWithPoints;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ColonyWorthRanking implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONY_WORTH';

    public function __construct(private DatabaseUiFactoryInterface $databaseUiFactory, private ColonyRepositoryInterface $colonyRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setNavigation([
            [
                'url' => 'database.php',
                'title' => 'Datenbank'
            ],
            [
                'url' => sprintf('database.php?%s=1', static::VIEW_IDENTIFIER),
                'title' => 'Die Top 10 der Architekten'
            ]
        ]);
        $game->setPageTitle('/ Datenbank / Die Top 10 der Architekten');
        $game->showMacro('html/database.xhtml/top_architects');

        $netWorthPerUserArray = $this->getNetWorthPerUser($game);

        $game->setTemplateVar(
            'ARCHITECTS_LIST',
            array_map(
                fn (int $userId, float $points): DatabaseTopListWithPoints => $this->databaseUiFactory->createDatabaseTopListWithPoints(
                    $userId,
                    $this->floatPointsToPercentageString($points)
                ),
                array_keys($netWorthPerUserArray),
                array_values($netWorthPerUserArray)
            )
        );
    }

    /**
     * @return array<int, int>
     */
    private function getNetWorthPerUser(GameControllerInterface $game): array
    {
        $currentUser = $game->getUser();

        $resultSet = $this->colonyRepository->getColoniesNetWorth();

        $commodityAmountArray = [];

        $userArray = [];

        foreach ($resultSet as $entry) {
            $userId = (int)$entry['user_id'];
            $commodityId = (int)$entry['commodity_id'];
            $sum = (int)$entry['sum'];

            if (!array_key_exists($commodityId, $commodityAmountArray)) {
                $commodityAmountArray[$commodityId] = 0;
            }
            $commodityAmountArray[$commodityId] += $sum;

            if (!array_key_exists($userId, $userArray)) {
                $userArray[$userId] = [];
            }

            $userArray[$userId][$commodityId] = $sum;
        }

        $userNetWorthArray = [];

        foreach ($userArray as $userId => $data) {
            $userNetWorthArray[$userId] = 0;

            foreach ($data as $commodityId => $sum) {
                $userNetWorthArray[$userId] += $sum / $commodityAmountArray[$commodityId];
            }

            $userNetWorthArray[$userId] *= 100;

            if ($userId === $currentUser->getId()) {
                $game->setTemplateVar('USER_POINTS', $this->floatPointsToPercentageString($userNetWorthArray[$userId]));
            }
        }

        arsort($userNetWorthArray);

        return array_slice($userNetWorthArray, 0, 10, true);
    }

    private function floatPointsToPercentageString(float $points): string
    {
        return sprintf('%01.2f', $points);
    }
}
