<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;

final class ResearchProvider implements ViewComponentProviderInterface
{
    public function __construct(private TechlistRetrieverInterface $techlistRetriever)
    {
    }

    #[Override]
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
