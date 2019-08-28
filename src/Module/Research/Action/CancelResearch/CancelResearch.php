<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class CancelResearch implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CANCEL_CURRENT_RESEARCH';

    private $researchedRepository;

    public function __construct(
        ResearchedRepositoryInterface  $researchedRepository
    ) {
        $this->researchedRepository = $researchedRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $current_research = $game->getUser()->getCurrentResearch();

        if ($current_research) {
            $this->researchedRepository->delete($current_research);
        }
        $game->addInformation(_('Die laufende Forschung wurde abgebrochen'));
        $game->setView(GameController::DEFAULT_VIEW);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
