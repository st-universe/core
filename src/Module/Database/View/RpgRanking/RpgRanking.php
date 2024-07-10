<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\RpgRanking;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopListWithPoints;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class RpgRanking implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOP_RPG';

    public function __construct(private DatabaseUiFactoryInterface $databaseUiFactory, private KnPostRepositoryInterface $knPostRepository)
    {
    }

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
            'Die 10 bestbewerteten RPG-Schreiber'
        );
        $game->setPageTitle('/ Datenbank / Die 10 bestbewerteten RPG-Schreiber');
        $game->setViewTemplate('html/database/highscores/topRpg.twig');
        $game->setTemplateVar(
            'PRESTIGE_LIST',
            array_map(
                fn (array $data): DatabaseTopListWithPoints => $this->databaseUiFactory->createDatabaseTopListWithPoints($data['user_id'], (string) $data['votes']),
                $this->knPostRepository->getRpgVotesTop10()
            )
        );
        $game->setTemplateVar('USER_COUNT', $this->knPostRepository->getRpgVotesOfUser($game->getUser()));
    }
}
