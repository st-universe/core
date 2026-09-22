<?php

namespace Stu\Module\Control;

use BadMethodCallException;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ViewExecution;
use Stu\Module\Control\Router\FallbackRouteException;
use Stu\Module\Control\Router\FallbackRouterInterface;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\GameTurnRepositoryInterface;

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
        private readonly TwigPageInterface $twigPage,
        private readonly GameUserRoleCheckerInterface $gameUserRoleChecker,
        private readonly GameTurnRepositoryInterface $gameTurnRepository,
        private readonly GameResponseFinalizerInterface $gameResponseFinalizer,
        private readonly FallbackRouterInterface $fallbackRouter,
        private readonly GameSetupInterface $gameSetup,
        private readonly GameStateInterface $gameState,
        private readonly JavascriptExecutionInterface $javascriptExecution,
        private readonly SessionStringFactoryInterface $sessionStringFactory
    ) {
        $this->gameData = new GameData();
    }

    #[\Override]
    public function setView(ModuleEnum|string $view): void
    {
        if ($view instanceof ModuleEnum) {
            unset($this->gameData->viewContext[ViewContextTypeEnum::VIEW->value]);
            $this->setViewContext(ViewContextTypeEnum::MODULE_VIEW, $view);
        } else {
            $this->setViewContext(ViewContextTypeEnum::VIEW, $view);
        }
    }

    #[\Override]
    public function getViewContext(ViewContextTypeEnum $type): mixed
    {
        if (!array_key_exists($type->value, $this->gameData->viewContext)) {
            return null;
        }

        return $this->gameData->viewContext[$type->value];
    }

    #[\Override]
    public function setViewContext(ViewContextTypeEnum $type, mixed $value): void
    {
        $this->gameData->viewContext[$type->value] = $value;
    }

    #[\Override]
    public function setViewTemplate(string $viewTemplate): void
    {
        $this->gameSetup->setTemplateAndComponents($viewTemplate, $this);
    }

    #[\Override]
    public function setTemplateFile(string $template): void
    {
        $this->twigPage->setTemplate($template);
    }

    #[\Override]
    public function setMacroInAjaxWindow(string $macro): void
    {
        $this->gameData->macro = $macro;

        $this->setTemplateFile('html/ajaxwindow.twig');
    }

    #[\Override]
    public function showMacro(string $macro): void
    {
        $this->gameData->macro = $macro;

        $this->setTemplateFile('html/ajaxempty.twig');
    }

    #[\Override]
    public function getGameData(): GameData
    {
        return $this->gameData;
    }

    #[\Override]
    public function getInfo(): InformationWrapper
    {
        return $this->gameData->gameInformations;
    }

    #[\Override]
    public function setTemplateVar(string $key, mixed $variable): void
    {
        $this->twigPage->setVar($key, $variable);
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
    public function setNavigation(
        array $navigationItems
    ): GameControllerInterface {
        foreach ($navigationItems as $item) {
            $this->appendNavigationPart($item['url'], $item['title']);
        }

        return $this;
    }

    #[\Override]
    public function appendNavigationPart(
        string $url,
        string $title
    ): void {
        $this->gameData->siteNavigation[$url] = $title;
    }

    #[\Override]
    public function setPageTitle(string $title): void
    {
        $this->gameData->pagetitle = $title;
    }

    #[\Override]
    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when = JavascriptExecutionTypeEnum::BEFORE_RENDER): void
    {
        $this->javascriptExecution->addExecuteJS($value, $when);
    }

    #[\Override]
    public function getCurrentRound(): GameTurn
    {
        if ($this->gameData->currentRound === null) {
            $this->gameData->currentRound = $this->gameTurnRepository->getCurrent();
            if ($this->gameData->currentRound === null) {
                throw new BadMethodCallException('no current round existing');
            }
        }
        return $this->gameData->currentRound;
    }

    #[\Override]
    public function getSessionString(): string
    {
        return $this->sessionStringFactory->createSessionString($this->getUser());
    }

    #[\Override]
    public function getGameRequest(): GameRequest
    {
        return $this->gameRequest;
    }

    #[\Override]
    public function main(ModuleEnum $module, GameRequest $gameRequest): void {
        $this->setViewContext(ViewContextTypeEnum::MODULE_VIEW, $module);

        $this->gameRequest = $gameRequest;

        try {

            if (!$this->gameModuleAccessChecker->isAllowed($module, $this)) {
                header(self::REDIRECT_TO_DOMAIN_ROOT);
                exit;
            }

            if ($this->hasUser()) {
                $this->userLockChecker->check($this->getUser());
            }

            $callbackExecuted = $this->maintenanceLoginExecutor
                ->executeIfRequired($module, $this);

            $this->gameState->checkGameState($this->isAdmin());

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
    public function isAdmin(): bool
    {
        return $this->gameUserRoleChecker->isAdmin();
    }

    #[\Override]
    public function isNpc(): bool
    {
        return $this->gameUserRoleChecker->isNpc();
    }

    #[\Override]
    public function resetGameData(): void
    {
        $this->gameData = new GameData();
    }
}
