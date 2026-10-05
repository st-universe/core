<?php

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ViewExecution;
use Stu\Module\Control\Router\FallbackRouteException;
use Stu\Module\Control\Router\FallbackRouterInterface;
use Stu\Module\Game\Lib\GameTurnProviderInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\User;

final class GameController implements GameControllerInterface
{
    public const string DEFAULT_VIEW = 'DEFAULT_VIEW';

    private const string REDIRECT_TO_DOMAIN_ROOT = 'Location: /';

    private GameRequest $gameRequest;
    private GameData $gameData;

    public function __construct(
        private readonly SessionInterface $session,
        private readonly UserLockCheckerInterface $userLockChecker,
        private readonly GameModuleAccessCheckerInterface $gameModuleAccessChecker,
        private readonly MaintenanceLoginExecutorInterface $maintenanceLoginExecutor,
        private readonly CallbackExecution $callbackExecution,
        private readonly ViewExecution $viewExecution,
        private readonly GameUserRoleCheckerInterface $gameUserRoleChecker,
        private readonly GameResponseFinalizerInterface $gameResponseFinalizer,
        private readonly FallbackRouterInterface $fallbackRouter,
        private readonly GameStateInterface $gameState,
        private readonly GameTurnProviderInterface $gameTurnProvider
    ) {
        $this->gameData = new GameData();
    }

    #[\Override]
    public function getGameData(): GameData
    {
        return $this->gameData;
    }

    #[\Override]
    public function getUser(): User
    {
        $user = $this->session->getUser();

        if ($user === null) {
            throw new AccessViolationException('User not set');
        }
        return $user;
    }

    #[\Override]
    public function hasUser(): bool
    {
        return $this->session->getUser() !== null;
    }

    #[\Override]
    public function getGameRequest(): GameRequest
    {
        return $this->gameRequest;
    }

    #[\Override]
    public function main(ModuleEnum $module, GameRequest $gameRequest): void
    {
        $this->gameData->viewContext[ViewContextMetadataTypeEnum::MODULE_VIEW->value] = $module;

        $this->gameRequest = $gameRequest;

        try {

            if (!$this->gameModuleAccessChecker->isAllowed($module)) {
                header(self::REDIRECT_TO_DOMAIN_ROOT);
                exit;
            }

            if ($this->hasUser()) {
                $this->userLockChecker->check($this->getUser());
            }

            $callbackExecuted = $this->maintenanceLoginExecutor
                ->executeIfRequired($module, $this);

            $this->gameState->checkGameState($this->gameUserRoleChecker->isAdmin());

            if (!$callbackExecuted) {
                $this->callbackExecution->execute($module, $this);
            }
            $this->viewExecution->execute($module, $this);
        } catch (FallbackRouteException $e) {
            $this->fallbackRouter->showFallbackSite($e, $this);
        }

        ob_start();
        echo $this->gameResponseFinalizer->finalize($this, $gameRequest);
        ob_end_flush();
    }

    #[\Override]
    public function getInfo(): InformationWrapper
    {
        return $this->gameData->gameInformations;
    }

    #[\Override]
    public function getCurrentRound(): GameTurn
    {
        return $this->gameTurnProvider->getCurrentRound();
    }

    #[\Override]
    public function resetGameData(): void
    {
        $this->gameData = new GameData();
    }
}
