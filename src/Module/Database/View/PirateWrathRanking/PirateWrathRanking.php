<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\PirateWrathRanking;

use Override;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\HistoryRepositoryInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PirateWrathRanking implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOP_PIRATE_WRATH';

    public function __construct(private DatabaseUiFactoryInterface $databaseUiFactory, private PirateWrathRepositoryInterface $pirateWrathRepository, private GradientColorInterface $gradientColor, private UserRepositoryInterface $userRepository, private HistoryRepositoryInterface $historyRepository) {}

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
                static::VIEW_IDENTIFIER
            ),
            'Die 10 meistgehassten Siedler der Kazon'
        );
        $game->setPageTitle('/ Datenbank / Die 10 meistgehassten Siedler der Kazon');
        $game->setViewTemplate('html/database/highscores/topPirateWrath.twig');
        $wrathList = $this->pirateWrathRepository->getPirateWrathTop10();
        $wrathData = array_map(
            function ($wrathEntity): array {
                $userId = $wrathEntity->getUser()->getId();
                $wrath = $wrathEntity->getWrath();

                return [
                    'user' => $this->userRepository->find($userId),
                    'entry' => $this->databaseUiFactory->createDatabaseTopListWithColorGradient(
                        $userId,
                        $this->gradientColor->calculateGradientColor(
                            $wrath,
                            500, // Untergrenze
                            2000 // Obergrenze
                        )
                    ),
                ];
            },
            $wrathList
        );


        $game->setTemplateVar('PIRATE_WRATH_LIST', $wrathData);

        $game->setTemplateVar(
            'USER_WRATH',
            ($game->getUser()->getPirateWrath()?->getWrath() ?? PirateWrathManager::DEFAULT_WRATH) / 10
        );
        $game->setTemplateVar('DESTROYED_PIRATES', $this->historyRepository->getSumDestroyedByUser($game->getUser()->getId(), UserEnum::USER_NPC_KAZON));
        $game->setTemplateVar('DESTROYED_BY_PIRATES', $this->historyRepository->getSumDestroyedByUser(UserEnum::USER_NPC_KAZON, $game->getUser()->getId()));
    }
}
