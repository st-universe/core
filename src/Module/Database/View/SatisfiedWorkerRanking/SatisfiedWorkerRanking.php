<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\SatisfiedWorkerRanking;

use Override;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopListWithPoints;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

//TODO use 'composer require twig/cache-extra'
// see https://twig.symfony.com/doc/3.x/tags/cache.html
final class SatisfiedWorkerRanking implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SATISFIED_WORKER';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private DatabaseUiFactoryInterface $databaseUiFactory,
        private ColonyRepositoryInterface $colonyRepository,
        private ColonyLibFactoryInterface $colonyLibFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
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
                'url' => sprintf('database.php?%s=1', self::VIEW_IDENTIFIER),
                'title' => 'Die Top 10 der besten Arbeitgeber'
            ]
        ]);
        $game->setPageTitle('/ Datenbank / Die Top 10 besten Arbeitgeber');
        $game->setViewTemplate('html/database/highscores/topEmployers.twig');

        $game->setTemplateVar('USER_ID', $game->getUser()->getId());

        $game->setTemplateVar(
            'EMPLOYER_LIST',
            array_map(
                fn(array $data): DatabaseTopListWithPoints => $this->databaseUiFactory->createDatabaseTopListWithPoints($data['user_id'], (string)$data['satisfied']),
                $this->colonyRepository->getSatisfiedWorkerTop10()
            )
        );

        $this->setPointsForUser($game);
    }

    private function setPointsForUser(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $colonies = $user->getColonies();
        if ($colonies->isEmpty()) {
            return;
        }

        $satisfiedWorkers = 0;

        foreach ($colonies as $colony) {
            $workers = $colony->getWorkers();

            $colonyProduction = $this->colonyLibFactory->createColonyCommodityProduction($colony)->getProduction();
            if (array_key_exists(CommodityTypeConstants::COMMODITY_EFFECT_LIFE_STANDARD, $colonyProduction)) {
                $lifestandard = $colonyProduction[CommodityTypeConstants::COMMODITY_EFFECT_LIFE_STANDARD]->getProduction();
            } else {
                $lifestandard = 0;
            }

            $this->loggerUtil->log(sprintf('colony: %s, lifestandard: %d', $colony->getName(), $lifestandard));

            $satisfiedWorkers += min($workers, $lifestandard);
        }

        $game->setTemplateVar('USER_POINTS', (string) $satisfiedWorkers);
    }
}
