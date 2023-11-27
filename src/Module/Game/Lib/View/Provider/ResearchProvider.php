<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;
use Stu\Module\Research\TechlistRetrieverInterface;

final class ResearchProvider implements ViewComponentProviderInterface
{
    private TechlistRetrieverInterface $techlistRetriever;

    public function __construct(
        TechlistRetrieverInterface $techlistRetriever
    ) {
        $this->techlistRetriever = $techlistRetriever;
    }

    public function setTemplateVariables(GameControllerInterface $game): void
    {
        $user = $game->getUser();

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
