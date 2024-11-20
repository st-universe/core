<?php

namespace Stu\Module\Game\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContext;
use Stu\Orm\Entity\TutorialStepInterface;
use Stu\Orm\Repository\TutorialStepRepositoryInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;

final class TutorialProvider
{
    public function __construct(
        private UserTutorialRepositoryInterface $userTutorialRepository,
        private TutorialStepRepositoryInterface $tutorialStepRepository
    ) {}

    public function setTemplateVariables(
        ViewContext $viewContext,
        GameControllerInterface $game
    ): void {

        $user = $game->getUser();

        $userTutorials = $user->getTutorials();
        if ($userTutorials->isEmpty()) {
            return;
        }

        $userTutorial = $this->userTutorialRepository->findByUserAndViewContext($user, $viewContext);
        if ($userTutorial === null) {
            return;
        }

        $tutorialSteps = $this->tutorialStepRepository->findByUserAndViewContext($user, $viewContext);
        if ($tutorialSteps == []) {
            return;
        }

        $payloadArray = array_map(fn(TutorialStepInterface $tutorialStep): array => $this->convertTutorialStep($tutorialStep), $tutorialSteps);

        $game->addExecuteJS(sprintf(
            "initTutorialSteps('%s', %d);",
            json_encode($payloadArray),
            $userTutorial->getTutorialStep()->getId()
        ), GameEnum::JS_EXECUTION_AFTER_RENDER);
    }

    /** @return array<string, mixed> */
    private function convertTutorialStep(TutorialStepInterface $tutorialStep): array
    {
        $result = [];

        if ($tutorialStep->getElementIds() !== null) {
            $result['elementIds'] = array_map('trim', explode(',', $tutorialStep->getElementIds()));
        }

        if ($tutorialStep->getTitle() !== null) {
            $result['title'] = trim((string)json_encode($tutorialStep->getTitle()), '"');
        }

        if ($tutorialStep->getText() !== null) {
            $result['text'] = trim((string)json_encode($tutorialStep->getText()), '"');
        }

        if ($tutorialStep->getInnerUpdate() !== null) {
            $result['innerUpdate'] = $tutorialStep->getInnerUpdate();
        }

        if ($tutorialStep->getFallbackIndex() !== null) {
            $result['fallbackIndex'] = $tutorialStep->getFallbackIndex();
        }

        $result['previousid'] = $tutorialStep->getPreviousStepId();
        $result['nextid'] = $tutorialStep->getNextStepId();

        return $result;
    }
}
