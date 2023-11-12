<?php

declare(strict_types=1);

namespace Stu\Module\Research\View\Overview;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;

final class Overview implements ViewControllerInterface
{
    private TechlistRetrieverInterface $techlistRetriever;

    public function __construct(
        TechlistRetrieverInterface $techlistRetriever
    ) {
        $this->techlistRetriever = $techlistRetriever;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $game->appendNavigationPart(
            'research.php',
            _('Forschung')
        );
        $game->setPageTitle(_('/ Forschung'));
        $game->setTemplateFile('html/research.xhtml');

        $game->setTemplateVar(
            'RESEARCH_LIST',
            $this->techlistRetriever->getResearchList($user)
        );
        $game->setTemplateVar(
            'RESEARCHED_LIST',
            $this->techlistRetriever->getResearchedList($user)
        );
    }
}
