<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use request;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\AuthenticatedActionController;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

/**
 * Cancels the current research of a user
 */
final class CancelResearch extends AuthenticatedActionController
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_CURRENT_RESEARCH';

    public function __construct(
        private ResearchedRepositoryInterface $researchedRepository,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $id = request::getIntFatal('id');

        $currentResearch = $this->researchedRepository->getCurrentResearch($game->getUser());

        foreach ($currentResearch as $researched) {
            if ($researched->getId() === $id) {
                $this->researchedRepository->delete($researched);
                $game->getInfo()->addInformation('Die laufende Forschung wurde abgebrochen');

                $this->componentRegistration->addComponentUpdate(GameComponentEnum::RESEARCH);
            }
        }
        $game->setView(GameController::DEFAULT_VIEW);
    }
}
