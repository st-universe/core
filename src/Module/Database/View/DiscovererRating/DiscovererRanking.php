<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DiscovererRating;

use DatabaseTopListDiscover;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

final class DiscovererRanking implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TOP_DISCOVER';

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    public function __construct(
        DatabaseUserRepositoryInterface $databaseUserRepository
    ) {
        $this->databaseUserRepository = $databaseUserRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Die 10 besten Entdecker')
        );
        $game->setPageTitle(_('/ Datenbank / Die 10 besten Entdecker'));
        $game->showMacro('html/database.xhtml/top_research_user');

        $game->setTemplateVar('DISCOVERER_LIST', $this->getTopResearchUser());
    }

    private function getTopResearchUser()
    {
        return array_map(
            function (array $data): DatabaseTopListDiscover {
                return new DatabaseTopListDiscover($data);
            },
            $this->databaseUserRepository->getTopList()
        );
    }
}
