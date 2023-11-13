<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\StartResearch;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class StartResearch implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DO_RESEARCH';

    private ResearchedRepositoryInterface $researchedRepository;

    private TechlistRetrieverInterface $techlistRetriever;

    private StartResearchRequestInterface $startResearchRequest;

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
        $user = $game->getUser();

        $research = $this->techlistRetriever->canResearch($user, $this->startResearchRequest->getResearchId());
        if ($research === null) {
            $game->addInformation('Kann aktuell nicht erforscht werden');
            return;
        }

        $researches = $this->researchedRepository->getCurrentResearch($user);
        if (count($researches) > 1) {
            $this->researchedRepository->delete($researches[1]);
        }

        $researched = $this->researchedRepository->prototype();
        $researched->setActive($research->getPoints());
        $researched->setUser($game->getUser());
        $researched->setResearch($research);
        $researched->setFinished(0);

        $this->researchedRepository->save($researched);

        if (empty($researches)) {
            $game->addInformation(sprintf(_('%s wird erforscht'), $research->getName()));
        } else {
            $game->addInformation(sprintf(_('%s wird als nächstes erforscht'), $research->getName()));
        }

        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
