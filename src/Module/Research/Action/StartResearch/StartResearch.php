<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\StartResearch;

use AccessViolation;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameController;
use Stu\Control\GameControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class StartResearch implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DO_RESEARCH';

    private $researchedRepository;

    private $techlistRetriever;

    private $startResearchRequest;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        TechlistRetrieverInterface $techlistRetriever,
        StartResearchRequestInterface $startResearchRequest
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->techlistRetriever = $techlistRetriever;
        $this->startResearchRequest = $startResearchRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = (int)$game->getUser()->getId();
        $researchId = $this->startResearchRequest->getResearchId();

        $research = $this->techlistRetriever->getResearchList($userId)[$researchId] ?? null;
        if ($research === null) {
            throw new AccessViolation();
        }
        $current_research = $game->getUser()->getCurrentResearch();

        if ($current_research) {
            $this->researchedRepository->delete($current_research);

            $game->addInformation(_('Die laufende Forschung wurde abgebrochen'));
        }

        $researched = $this->researchedRepository->prototype();
        $researched->setActive($research->getPoints());
        $researched->setUserId($userId);
        $researched->setResearch($research);
        $researched->setFinished(0);

        $this->researchedRepository->save($researched);

        $game->addInformation(sprintf(_('%s wird erforscht'), $research->getName()));
        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
