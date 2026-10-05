<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\CancelResearch;

use request;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\AuthenticatedActionController;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameController;
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
    public function handle(ActionControllerContext $context): void
    {
        $id = request::getIntFatal('id');

        $currentResearch = $this->researchedRepository->getCurrentResearch($context->getUser());

        foreach ($currentResearch as $researched) {
            if ($researched->getId() === $id) {
                $this->researchedRepository->delete($researched);
                $context->getInfo()->addInformation('Die laufende Forschung wurde abgebrochen');

                $this->componentRegistration->addComponentUpdate(GameComponentEnum::RESEARCH);
            }
        }
        $context->setView(GameController::DEFAULT_VIEW);
    }
}
