<?php

declare(strict_types=1);

namespace Stu\Module\Research\View\Overview;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;

final class Overview implements ViewControllerInterface
{

    private $techlistRetriever;

    public function __construct(
        TechlistRetrieverInterface $techlistRetriever
    ) {
        $this->techlistRetriever = $techlistRetriever;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = (int) $game->getUser()->getId();

        $game->appendNavigationPart(
            'research.php',
            _('Datenbank')
        );
        $game->setPageTitle(_('/ Forschung'));
        $game->setTemplateFile('html/research.xhtml');

        $game->setTemplateVar(
            'RESEARCH_LIST',
            $this->techlistRetriever->getResearchList($userId)
        );
        $game->setTemplateVar(
            'FINISHED_LIST',
            $this->techlistRetriever->getFinishedResearchList($userId)
        );
    }
}
