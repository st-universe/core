<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\PirateWrathRanking;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Repository\PirateWrathRepositoryInterface;
use Stu\Orm\Entity\PirateWrathInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PirateWrathRanking implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TOP_PIRATE_WRATH';

    private DatabaseUiFactoryInterface $databaseUiFactory;

    private UserRepositoryInterface $userRepository;

    private PirateWrathRepositoryInterface $pirateWrathRepository;

    private GradientColorInterface $gradientColor;

    public function __construct(
        DatabaseUiFactoryInterface $databaseUiFactory,
        PirateWrathRepositoryInterface $pirateWrathRepository,
        GradientColorInterface $gradientColor,
        UserRepositoryInterface $userRepository
    ) {
        $this->databaseUiFactory = $databaseUiFactory;
        $this->pirateWrathRepository = $pirateWrathRepository;
        $this->gradientColor = $gradientColor;
        $this->userRepository = $userRepository;
    }

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
            'Die 10 größten Feinde der Kazon'
        );
        $game->setPageTitle('/ Datenbank / Die 10 größten Feinde der Kazon');
        $game->showMacro('html/database.xhtml/top_pirate_wrath_user');
        $wrathList = $this->pirateWrathRepository->getPirateWrathTop10();
        $wrathData = array_map(
            function ($wrathEntity) {
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
            ($game->getUser()->getPirateWrath()?->getWrath() ?? PirateWrathInterface::DEFAULT_WRATH) / 10
        );
    }
}
