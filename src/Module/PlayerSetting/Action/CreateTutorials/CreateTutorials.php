<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\CreateTutorials;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;

final class CreateTutorials implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_TUTORIALS';

    public function __construct(private UserTutorialRepositoryInterface $userTutorialRepository, private TutorialStepRepositoryInterface $tutorialStepRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $user = $context->getUser();

        $this->userTutorialRepository->truncateByUser($user);

        $firstSteps = $this->tutorialStepRepository->findAllFirstSteps();

        foreach ($firstSteps as $step) {
            $userTutorial = $this->userTutorialRepository->prototype();
            $userTutorial->setUser($user);
            $userTutorial->setTutorialStep($step);

            $this->userTutorialRepository->save($userTutorial);
        }


        $context->getInfo()->addInformation(_('Tutorial wurde neu gestartet'));
    }


    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
