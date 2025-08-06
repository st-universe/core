<?php

namespace Stu\Module\Control\Component;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;

class CallbackExecution
{
    public function __construct(
        private readonly ControllerDiscoveryInterface $controllerDiscovery,
        private readonly AccessCheckInterface $accessCheck,
        private readonly StuTime $stuTime,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function execute(ModuleEnum $module, GameControllerInterface $game): void
    {
        $startTime = $this->stuTime->hrtime();

        try {
            $this->executeCallback($module, $game);
        } catch (SanityCheckException $e) {
            $game->getGameRequest()->addError($e);
        } catch (EntityLockedException $e) {
            $game->getInfo()->addInformation($e->getMessage());
        }

        $actionMs = $this->stuTime->hrtime() - $startTime;
        $game->getGameRequest()->setActionMs((int)$actionMs / 1_000_000);
    }

    private function executeCallback(ModuleEnum $module, GameControllerInterface $game): void
    {
        $actions = $this->controllerDiscovery->getControllers($module, false);

        foreach ($actions as $actionIdentifier => $controller) {
            if (!request::has($actionIdentifier)) {
                continue;
            }

            $game->getGameRequest()->setAction($actionIdentifier);

            if ($this->accessCheck->checkUserAccess($controller, $game)) {
                $controller->handle($game);
                $this->entityManager->flush();
            }
            break;
        }
    }
}
