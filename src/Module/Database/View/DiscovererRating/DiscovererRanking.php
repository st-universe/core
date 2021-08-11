<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DiscovererRating;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Database\Lib\DatabaseTopListDiscover;
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
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Die 10 besten Entdecker')
        );
        $game->setPageTitle(_('/ Datenbank / Die 10 besten Entdecker'));
        $game->showMacro('html/database.xhtml/top_research_user');

        $topList = $this->getTopResearchUser();
        $game->setTemplateVar('DISCOVERER_LIST', $topList);

        $containsUser = false;
        foreach ($topList as $element) {
            if ($element->getUserId() === $game->getUser()->getId()) {
                $containsUser = true;
            }
        }

        if (!$containsUser) {
            $game->setTemplateVar('USER_COUNT', $this->databaseUserRepository->getCountForUser($game->getUser()->getId()));
        }
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
