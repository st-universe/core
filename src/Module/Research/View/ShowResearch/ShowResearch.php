<?php

declare(strict_types=1);

namespace Stu\Module\Research\View\ShowResearch;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Research\SelectedTechFactoryInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ShowResearch implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RESEARCH';

    public function __construct(private ShowResearchRequestInterface $showResearchRequest, private TechlistRetrieverInterface $techlistRetriever, private ResearchedRepositoryInterface $researchedRepository, private SelectedTechFactoryInterface $talFactory)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $researchId = $this->showResearchRequest->getResearchId();

        $research = $this->techlistRetriever->getResearchList($user)[$researchId] ?? null;
        if ($research === null) {
            $result = $this->researchedRepository->getFor($researchId, $user->getId());

            if ($result === null) {
                throw new AccessViolationException();
            }
            $research = $result->getResearch();
        }

        $game->setPageTitle(sprintf('Forschung: %s', $research->getName()));
        $game->setMacroInAjaxWindow('html/research/researchDetails.twig');
        $game->setTemplateVar(
            'TAL_RESEARCH',
            $this->talFactory->createSelectedTech($research, $game->getUser())
        );
    }
}
