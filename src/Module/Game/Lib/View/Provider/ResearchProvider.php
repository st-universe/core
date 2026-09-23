<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Research\TechlistRetrieverInterface;

final class ResearchProvider implements ViewComponentProviderInterface
{
    public function __construct(private TechlistRetrieverInterface $techlistRetriever) {}

    #[\Override]
    public function setTemplateVariables(ViewControllerContext $game): void
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
