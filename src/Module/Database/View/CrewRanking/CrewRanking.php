<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\CrewRanking;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopListCrew;
use Stu\Module\Database\Lib\DatabaseUiFactoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class CrewRanking implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TOP_CREW';

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private DatabaseUiFactoryInterface $databaseUiFactory;

    public function __construct(
        DatabaseUiFactoryInterface $databaseUiFactory,
        ShipCrewRepositoryInterface $shipCrewRepository
    ) {
        $this->shipCrewRepository = $shipCrewRepository;
        $this->databaseUiFactory = $databaseUiFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setNavigation([
            [
                'url' => 'database.php',
                'title' => 'Datenbank'
            ],
            [
                'url' => sprintf('database.php?%s=1', static::VIEW_IDENTIFIER),
                'title' => 'Die Top 10 der Ausbilder'
            ]
        ]);
        $game->setPageTitle('/ Datenbank / Die Top 10 der Ausbilder');
        $game->showMacro('html/database.xhtml/top_crew_user');

        $game->setTemplateVar(
            'CREWS_LIST',
            array_map(
                fn (array $data): DatabaseTopListCrew => $this->databaseUiFactory->createDatabaseTopListCrew($data),
                $this->shipCrewRepository->getCrewsTop10()
            )
        );
    }
}
