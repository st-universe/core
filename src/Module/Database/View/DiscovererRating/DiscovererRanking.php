<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DiscovererRating;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopListWithPoints;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class DiscovererRanking implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TOP_DISCOVER';

    public function __construct(private DatabaseUiFactoryInterface $databaseUiFactory, private DatabaseUserRepositoryInterface $databaseUserRepository)
    {
    }

    #[\Override]
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
            'Die 10 besten Entdecker'
        );
        $game->setPageTitle('/ Datenbank / Die 10 besten Entdecker');
        $game->setViewTemplate('html/database/highscores/topDiscover.twig');
        $game->setTemplateVar(
            'DISCOVERER_LIST',
            array_map(
                fn (array $data): DatabaseTopListWithPoints => $this->databaseUiFactory->createDatabaseTopListWithPoints(
                    $data['user_id'],
                    (string) $data['points'],
                    $data['timestamp'],
                ),
                $this->databaseUserRepository->getTopList()
            )
        );
        $game->setTemplateVar('USER_COUNT', $this->databaseUserRepository->getCountForUser($game->getUser()->getId()));
    }
}
