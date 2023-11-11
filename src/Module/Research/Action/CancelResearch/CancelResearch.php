<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use request;
use Stu\Module\Control\AuthenticatedActionController;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

/**
 * Cancels the current research of a user
 */
final class CancelResearch extends AuthenticatedActionController
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_CURRENT_RESEARCH';

    private ResearchedRepositoryInterface $researchedRepository;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository
    ) {
        $this->researchedRepository = $researchedRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $id = request::getIntFatal('id');

        $currentResearch = $this->researchedRepository->getCurrentResearch($game->getUser());

        foreach ($currentResearch as $researched) {
            if ($researched->getId() === $id) {
                $this->researchedRepository->delete($researched);
                $game->addInformation('Die laufende Forschung wurde abgebrochen');
            }
        }
        $game->setView(GameController::DEFAULT_VIEW);
    }
}
