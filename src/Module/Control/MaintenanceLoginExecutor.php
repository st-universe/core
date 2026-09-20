<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use request;
use Stu\Component\Game\GameStateEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Game\RedirectionException;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Control\Component\CallbackExecutionInterface;

final class MaintenanceLoginExecutor implements MaintenanceLoginExecutorInterface
{
    private const string LOGIN_ACTION_IDENTIFIER = 'B_LOGIN';

    public function __construct(
        private readonly CallbackExecutionInterface $callbackExecution,
        private readonly GameStateInterface $gameState,
        private readonly SessionInterface $session
    ) {}

    #[\Override]
    public function executeIfRequired(ModuleEnum $module, GameControllerInterface $game): bool
    {
        if (
            $module !== ModuleEnum::INDEX
            || !request::has(self::LOGIN_ACTION_IDENTIFIER)
            || $this->gameState->getGameState() !== GameStateEnum::MAINTENANCE
        ) {
            return false;
        }

        try {
            $this->callbackExecution->execute($module, $game);
        } catch (RedirectionException $e) {
            if (!$game->isAdmin()) {
                $this->session->logout();
                throw new MaintenanceGameStateException($e->getMessage(), $e->getCode(), $e);
            }

            throw $e;
        }

        return true;
    }
}
