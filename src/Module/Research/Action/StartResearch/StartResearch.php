<?php

declare(strict_types=1);

namespace Stu\Module\Research\Action\StartResearch;

use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

final class StartResearch implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DO_RESEARCH';

    public function __construct(
        private ResearchedRepositoryInterface $researchedRepository,
        private TechlistRetrieverInterface $techlistRetriever,
        private StartResearchRequestInterface $startResearchRequest,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $user = $context->getUser();

        $research = $this->techlistRetriever->canResearch($user, $this->startResearchRequest->getResearchId());
        if ($research === null) {
            $context->getInfo()->addInformation('Kann aktuell nicht erforscht werden');
            return;
        }

        $researches = $this->researchedRepository->getCurrentResearch($user);
        if (count($researches) > 1) {
            $this->researchedRepository->delete($researches[1]);
        }

        $researched = $this->researchedRepository->prototype();
        $researched->setActive($research->getPoints());
        $researched->setUser($context->getUser());
        $researched->setResearch($research);
        $researched->setFinished(0);

        $this->researchedRepository->save($researched);

        if ($researches === []) {
            $context->getInfo()->addInformation(sprintf(_('%s wird erforscht'), $research->getName()));
        } else {
            $context->getInfo()->addInformation(sprintf(_('%s wird als Nächstes erforscht'), $research->getName()));
        }

        $context->setView(GameController::DEFAULT_VIEW);

        $this->componentRegistration->addComponentUpdate(GameComponentEnum::RESEARCH);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
