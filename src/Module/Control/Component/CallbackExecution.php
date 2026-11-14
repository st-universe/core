<?php

namespace Stu\Module\Control\Component;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Borg\BorgAssimilationActionGuard;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionContextFactoryInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Action\PromoteCrew\PromoteCrew;
use Stu\Module\Spacecraft\Action\RenameCrewman\RenameCrewman;
use Stu\Module\Spacecraft\View\ShowCrewmanDetails\ShowCrewmanDetails;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

class CallbackExecution implements CallbackExecutionInterface
{
    public function __construct(
        private readonly ControllerDiscoveryInterface $controllerDiscovery,
        private readonly AccessCheckInterface $accessCheck,
        private readonly ActionContextFactoryInterface $actionContextFactory,
        private readonly StuTime $stuTime,
        private readonly EntityManagerInterface $entityManager,
        private readonly ?BorgAssimilationActionGuard $assimilationActionGuard = null
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
        $game->getGameRequest()->setActionMs((int)ceil($actionMs / 1_000_000));
    }

    private function executeCallback(ModuleEnum $module, GameControllerInterface $game): void
    {
        $actions = $this->controllerDiscovery->getControllers($module, false);

        /** @var ActionControllerInterface $controller */
        foreach ($actions as $actionIdentifier => $controller) {
            if (!request::has($actionIdentifier)) {
                continue;
            }

            $game->getGameRequest()->setAction($actionIdentifier);

            if ($this->accessCheck->checkUserAccess($controller, $game->getInfo())) {
                $context = $this->actionContextFactory->createActionContext($game);
                $this->checkAssimilationAction($module, $actionIdentifier, $game, $context);
                $controller->handle($context);
                $this->entityManager->flush();
            }
            break;
        }
    }

    private function checkAssimilationAction(ModuleEnum $module, string $action, GameControllerInterface $game, ActionControllerContext $context): void
    {
        if ($this->assimilationActionGuard === null || !$game->hasUser()) {
            return;
        }
        try {
            $this->assimilationActionGuard->check($module, $action, $game->getUser());
        } catch (EntityLockedException $exception) {
            if (in_array($module, [ModuleEnum::SHIP, ModuleEnum::STATION], true) && request::indInt('id') > 0) {
                $context->setView(in_array($action, [RenameCrewman::ACTION_IDENTIFIER, PromoteCrew::ACTION_IDENTIFIER], true)
                    ? ShowCrewmanDetails::VIEW_IDENTIFIER : ShowSpacecraft::VIEW_IDENTIFIER);
            }
            throw $exception;
        }
    }
}
