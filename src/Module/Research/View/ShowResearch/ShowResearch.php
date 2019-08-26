<?php

declare(strict_types=1);

namespace Stu\Module\Research\View\ShowResearch;

use AccessViolation;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Research\TalFactoryInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class ShowResearch implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_RESEARCH';

    private $showResearchRequest;

    private $techlistRetriever;

    private $researchedRepository;

    private $talFactory;

    public function __construct(
        ShowResearchRequestInterface $showResearchRequest,
        TechlistRetrieverInterface $techlistRetriever,
        ResearchedRepositoryInterface $researchedRepository,
        TalFactoryInterface $talFactory
    ) {
        $this->showResearchRequest = $showResearchRequest;
        $this->techlistRetriever = $techlistRetriever;
        $this->researchedRepository = $researchedRepository;
        $this->talFactory = $talFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = (int)$game->getUser()->getId();
        $researchId = $this->showResearchRequest->getResearchId();

        $research = $this->techlistRetriever->getResearchList($userId)[$researchId] ?? null;
        if ($research === null) {
            $result = $this->researchedRepository->getFor($researchId, $userId);

            if ($result === null) {
                throw new AccessViolation();
            }
            $research = $result->getResearch();
        }

        $game->setPageTitle(sprintf('Forschung: %s', $research->getName()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/researchmacros.xhtml/details');
        $game->setTemplateVar(
            'SELECTED_RESEARCH',
            $this->talFactory->createTalSelectedTech($research, $game->getUser())
        );
    }
}
