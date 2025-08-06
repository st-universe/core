<?php

namespace Stu\Module\Control\Component;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\ControllerInterface;
use Stu\Module\Control\GameController;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewWithTutorialInterface;

class ViewExecution
{
    public function __construct(
        private readonly ControllerDiscoveryInterface $controllerDiscovery,
        private readonly AccessCheckInterface $accessCheck,
        private readonly TutorialProvider $tutorialProvider,
        private readonly StuTime $stuTime,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function execute(ModuleEnum $module, GameControllerInterface $game): void
    {
        $startTime = $this->stuTime->hrtime();

        try {
            $this->executeView($module, $game);
        } catch (SanityCheckException $e) {
            $game->getGameRequest()->addError($e);
        } catch (EntityLockedException $e) {
            $game->getInfo()->addInformation($e->getMessage());
            $game->setMacroInAjaxWindow('');
        }

        $viewMs = $this->stuTime->hrtime() - $startTime;
        $game->getGameRequest()->setViewMs((int)$viewMs / 1_000_000);
    }

    private function executeView(ModuleEnum $module, GameControllerInterface $game): void
    {
        $viewFromContext = $game->getViewContext(ViewContextTypeEnum::VIEW);

        $views = $this->controllerDiscovery->getControllers($module, true);

        foreach ($views as $viewIdentifier => $controller) {
            if (
                request::has($viewIdentifier)
                || $viewIdentifier === $viewFromContext
            ) {
                $game->getGameRequest()->setView($viewIdentifier);

                if ($this->accessCheck->checkUserAccess($controller, $game)) {
                    $this->handleView($controller, $game);
                    return;
                }
                break;
            }
        }

        $view = $views[GameController::DEFAULT_VIEW] ?? null;

        if (
            $view !== null
            && $this->accessCheck->checkUserAccess($view, $game)
        ) {
            $this->handleView($view, $game);
        }
    }

    private function handleView(ControllerInterface $view, GameControllerInterface $game): void
    {
        $view->handle($game);

        if ($view instanceof ViewWithTutorialInterface) {
            $this->tutorialProvider->setTemplateVariables(
                $view->getViewContext(),
                $game
            );
        }

        $this->entityManager->flush();
    }
}
