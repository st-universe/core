<?php

namespace Stu\Module\Control;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use request;
use RuntimeException;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\ResetGameStateException;
use Stu\Exception\SanityCheckException;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionLoginInterface;
use Stu\Lib\UserLockedException;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\Router\FallbackRouteException;
use Stu\Module\Control\Router\FallbackRouterInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Game\Lib\TutorialProvider;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use SysvSemaphore;
use Ubench;

final class GameController implements GameControllerInterface
{
    public const string DEFAULT_VIEW = 'DEFAULT_VIEW';

    private GameData $gameData;

    public function __construct(
        private readonly ControllerDiscovery $controllerDiscovery,
        private readonly AccessCheckInterface $accessCheck,
        private readonly SessionInterface $session,
        private readonly SessionLoginInterface $sessionLogin,
        private readonly TwigPageInterface $twigPage,
        private readonly DatabaseUserRepositoryInterface $databaseUserRepository,
        private readonly StuConfigInterface $stuConfig,
        private readonly GameTurnRepositoryInterface $gameTurnRepository,
        private readonly GameConfigRepositoryInterface $gameConfigRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepositoryInterface $userRepository,
        private readonly ComponentSetupInterface $componentSetup,
        private readonly Ubench $benchmark,
        private readonly CreateDatabaseEntryInterface $createDatabaseEntry,
        private readonly GameRequestRepositoryInterface $gameRequestRepository,
        private readonly GameTwigRendererInterface $gameTwigRenderer,
        private readonly FallbackRouterInterface $fallbackRouter,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly GameRequestSaverInterface $gameRequestSaver,
        private readonly GameSetupInterface $gameSetup,
        private readonly TutorialProvider $tutorialProvider,
        private readonly SessionStringFactoryInterface $sessionStringFactory,
        private readonly BenchmarkResultInterface $benchmarkResult
    ) {
        $this->gameData = new GameData();
    }

    #[Override]
    public function setView(ModuleEnum|string $view): void
    {
        if ($view instanceof ModuleEnum) {
            $this->setViewContext(ViewContextTypeEnum::MODULE_VIEW, $view);
        } else {
            $this->setViewContext(ViewContextTypeEnum::VIEW, $view);
        }
    }

    #[Override]
    public function getViewContext(ViewContextTypeEnum $type): mixed
    {
        if (!array_key_exists($type->value, $this->gameData->viewContext)) {
            return null;
        }

        return $this->gameData->viewContext[$type->value];
    }

    #[Override]
    public function setViewContext(ViewContextTypeEnum $type, mixed $value): void
    {
        $this->gameData->viewContext[$type->value] = $value;
    }

    #[Override]
    public function getGameState(): int
    {
        return $this->getGameConfig()[GameEnum::CONFIG_GAMESTATE]->getValue();
    }

    #[Override]
    public function setViewTemplate(string $viewTemplate): void
    {
        $isSwitch = request::has('switch');
        if ($isSwitch) {
            $this->setTemplateFile('html/view/breadcrumbAndView.twig');
            $this->setTemplateVar('VIEW_TEMPLATE', $viewTemplate);
        } else {
            $this->gameSetup->setTemplateAndComponents($viewTemplate, $this);
        }
    }

    #[Override]
    public function setTemplateFile(string $template): void
    {
        $this->twigPage->setTemplate($template);
    }

    #[Override]
    public function setMacroInAjaxWindow(string $macro): void
    {
        $this->gameData->macro = $macro;

        $this->setTemplateFile('html/ajaxwindow.twig');
    }

    #[Override]
    public function showMacro(string $macro): void
    {
        $this->gameData->macro = $macro;

        $this->setTemplateFile('html/ajaxempty.twig');
    }

    #[Override]
    public function getMacro(): string
    {
        return $this->gameData->macro;
    }

    #[Override]
    public function addInformationf(string $text, ...$args): InformationInterface
    {
        $this->addInformation(vsprintf(
            $text,
            $args
        ));

        return $this;
    }

    #[Override]
    public function addInformation(?string $information): InformationInterface
    {
        $this->gameData->gameInformations->addInformation($information);

        return $this;
    }

    #[Override]
    public function addInformationMerge(array $info): void
    {
        $this->gameData->gameInformations->addInformationArray($info, true);
    }

    /** @param array<string> $info */
    private function addInformationMergeDown(array $info): void
    {
        $this->gameData->gameInformations->addInformationArray($info);
    }

    #[Override]
    public function addInformationWrapper(?InformationWrapper $informations, bool $isHead = false): void
    {
        if ($informations === null) {
            return;
        }

        if ($isHead) {
            $this->addInformationMerge($informations->getInformations());
        } else {
            $this->addInformationMergeDown($informations->getInformations());
        }
    }

    #[Override]
    public function getInformation(): array
    {
        return $this->gameData->gameInformations->getInformations();
    }

    #[Override]
    public function getTargetLink(): ?TargetLink
    {
        return $this->gameData->targetLink;
    }

    #[Override]
    public function setTargetLink(TargetLink $targetLink): GameControllerInterface
    {
        $this->gameData->targetLink = $targetLink;

        return $this;
    }

    #[Override]
    public function setTemplateVar(string $key, mixed $variable): void
    {
        $this->twigPage->setVar($key, $variable);
    }

    #[Override]
    public function getUser(): User
    {
        $user = $this->session->getUser();

        if ($user === null) {
            throw new AccessViolationException('User not set');
        }
        return $user;
    }

    #[Override]
    public function hasUser(): bool
    {
        return $this->session->getUser() !== null;
    }

    /**
     * @return array<int, GameConfig>
     */
    private function getGameConfig(): array
    {
        if ($this->gameData->gameConfig === null) {
            $this->gameData->gameConfig = [];

            foreach ($this->gameConfigRepository->findAll() as $item) {
                $this->gameData->gameConfig[$item->getOption()] = $item;
            }
        }
        return $this->gameData->gameConfig;
    }

    #[Override]
    public function setNavigation(
        array $navigationItems
    ): GameControllerInterface {
        foreach ($navigationItems as $item) {
            $this->appendNavigationPart($item['url'], $item['title']);
        }

        return $this;
    }

    #[Override]
    public function appendNavigationPart(
        string $url,
        string $title
    ): void {
        $this->gameData->siteNavigation[$url] = $title;
    }

    #[Override]
    public function getNavigation(): array
    {
        return $this->gameData->siteNavigation;
    }

    #[Override]
    public function getPageTitle(): string
    {
        return $this->gameData->pagetitle;
    }

    #[Override]
    public function setPageTitle(string $title): void
    {
        $this->gameData->pagetitle = $title;
    }

    #[Override]
    public function getExecuteJS(int $when): ?array
    {
        if (!array_key_exists($when, $this->gameData->execjs)) {
            return null;
        }

        return $this->gameData->execjs[$when];
    }

    #[Override]
    public function addExecuteJS(string $value, int $when = GameEnum::JS_EXECUTION_BEFORE_RENDER): void
    {
        switch ($when) {
            case GameEnum::JS_EXECUTION_BEFORE_RENDER:
                $this->gameData->execjs[$when][] = $value;
                break;
            case GameEnum::JS_EXECUTION_AFTER_RENDER:
                $this->gameData->execjs[$when][] = $value;
                break;
            case GameEnum::JS_EXECUTION_AJAX_UPDATE:
                $this->gameData->execjs[$when][] = $value;
                break;
        }
    }

    #[Override]
    public function redirectTo(string $href): void
    {
        $this->gameRequestSaver->save($this->getGameRequest());
        $this->entityManager->flush();
        $this->entityManager->commit();
        header('Location: ' . $href);
        exit;
    }

    #[Override]
    public function getCurrentRound(): GameTurn
    {
        if ($this->gameData->currentRound === null) {
            $this->gameData->currentRound = $this->gameTurnRepository->getCurrent();
            if ($this->gameData->currentRound === null) {
                throw new RuntimeException('no current round existing');
            }
        }
        return $this->gameData->currentRound;
    }

    #[Override]
    public function checkDatabaseItem(?int $databaseEntryId): void
    {
        if (
            $databaseEntryId === null
            || $databaseEntryId === 0
        ) {
            return;
        }

        $userId = $this->getUser()->getId();

        if (
            $databaseEntryId > 0
            && !$this->databaseUserRepository->exists($userId, $databaseEntryId)
        ) {
            $entry = $this->createDatabaseEntry->createDatabaseEntryForUser($this->getUser(), $databaseEntryId);

            if ($entry !== null) {
                $this->gameData->achievements[] = sprintf(
                    _('Neuer Datenbankeintrag: %s (+%d Punkte)'),
                    $entry->getDescription(),
                    $entry->getCategory()->getPoints()
                );
            }
        }
    }

    #[Override]
    public function getAchievements(): array
    {
        return $this->gameData->achievements;
    }

    #[Override]
    public function getSessionString(): string
    {
        return $this->sessionStringFactory->createSessionString($this->getUser());
    }

    #[Override]
    public function sessionAndAdminCheck(): void
    {
        $this->session->createSession(true);

        if (!$this->isAdmin()) {
            header('Location: /');
        }
    }

    #[Override]
    public function getGameRequestId(): string
    {
        return $this->getGameRequest()->getRequestId();
    }

    #[Override]
    public function getGameRequest(): GameRequest
    {
        if ($this->gameData->gameRequest === null) {
            $gameRequest = $this->gameRequestRepository->prototype();
            $gameRequest->setTime(time());
            $gameRequest->setTurnId($this->getCurrentRound());
            $gameRequest->setParameterArray(request::isPost() ? request::postvars() : request::getvars());
            $gameRequest->setRequestId($this->uuidGenerator->genV4());

            if ($this->hasUser()) {
                $gameRequest->setUserId($this->getUser());
            }

            $this->gameData->gameRequest = $gameRequest;
        }
        return $this->gameData->gameRequest;
    }

    #[Override]
    public function main(
        ModuleEnum $module,
        bool $session_check = true,
        bool $admin_check = false,
        bool $npc_check = false,
    ): void {
        $this->setViewContext(ViewContextTypeEnum::MODULE_VIEW, $module);

        $gameRequest = $this->getGameRequest();
        $gameRequest->setModule($module->value);

        try {
            $this->session->createSession($session_check);

            if ($session_check === false) {
                $this->sessionLogin->checkLoginCookie();
            }

            if ($module === ModuleEnum::NPC && (!$this->isNpc() && !$this->isAdmin())) {
                header('Location: /');
                exit;
            }

            if ($module === ModuleEnum::ADMIN && !$this->isAdmin()) {
                header('Location: /');
                exit;
            }

            $this->checkUserLock($gameRequest);
            $this->checkGameState();

            // log action & view and time they took
            $startTime = hrtime(true);
            try {
                $this->executeCallback($module, $gameRequest);
            } catch (SanityCheckException $e) {
                $gameRequest->addError($e);
            } catch (EntityLockedException $e) {
                $this->addInformation($e->getMessage());
            }
            $actionMs = hrtime(true) - $startTime;

            $startTime = hrtime(true);
            try {
                $this->executeView($module, $gameRequest);
            } catch (SanityCheckException $e) {
                $gameRequest->addError($e);
            } catch (EntityLockedException $e) {
                $this->addInformation($e->getMessage());
                $this->setMacroInAjaxWindow('');
            }
            $viewMs = hrtime(true) - $startTime;

            $gameRequest->setActionMs((int)$actionMs / 1_000_000);
            $gameRequest->setViewMs((int)$viewMs / 1_000_000);
        } catch (SessionInvalidException) {
            session_destroy();

            if (request::isAjaxRequest()) {
                header('HTTP/1.0 400');
            } else {
                header('Location: /');
            }
            return;
        } catch (FallbackRouteException $e) {
            $this->fallbackRouter->showFallbackSite($e, $this);
        }

        $isTemplateSet = $this->twigPage->isTemplateSet();

        if (!$isTemplateSet) {
            StuLogger::logf('NO TEMPLATE FILE SPECIFIED, Method: %s', request::isPost() ? 'POST' : 'GET');
            StuLogger::log(print_r(request::isPost() ? request::postvars() : request::getvars(), true));
        }

        $user = $this->hasUser()
            ? $this->getUser()
            : null;

        $this->componentSetup->setup($this);

        // RENDER!
        $startTime = hrtime(true);
        $renderResult = $this->gameTwigRenderer->render($this, $user);
        $renderMs = hrtime(true) - $startTime;

        ob_start();
        echo $renderResult;
        ob_end_flush();

        // SAVE META DATA
        $gameRequest->setRenderMs((int)$renderMs / 1_000_000);

        $this->gameRequestSaver->save($gameRequest);
    }

    private function checkUserLock(GameRequest $gameRequest): void
    {
        if ($this->hasUser()) {
            $user = $this->getUser();
            $gameRequest->setUserId($user);

            $userLock = $user->getUserLock();
            if ($this->getUser()->isLocked() && $userLock !== null) {
                $this->session->logout();

                throw new UserLockedException(
                    _('Dein Spieleraccount wurde gesperrt'),
                    sprintf(_('Dein Spieleraccount ist noch für %d Ticks gesperrt. Begründung: %s'), $userLock->getRemainingTicks(), $userLock->getReason())
                );
            }
        }
    }

    private function checkGameState(): void
    {
        $gameState = $this->getGameState();

        if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE && !$this->isAdmin()) {
            throw new MaintenanceGameStateException();
        }

        if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_RESET) {
            throw new ResetGameStateException();
        }

        if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_RELOCATION) {
            throw new RelocationGameStateException();
        }
    }

    #[Override]
    public function isAdmin(): bool
    {
        if (!$this->hasUser()) {
            return false;
        }

        return in_array(
            $this->getUser()->getId(),
            $this->stuConfig->getGameSettings()->getAdminIds(),
            true
        );
    }

    #[Override]
    public function isNpc(): bool
    {
        if (!$this->hasUser()) {
            return false;
        }

        return $this->getUser()->isNpc();
    }

    #[Override]
    public function isSemaphoreAlreadyAcquired(int $key): bool
    {
        return array_key_exists($key, $this->gameData->semaphores);
    }

    #[Override]
    public function addSemaphore(int $key, SysvSemaphore $semaphore): void
    {
        $this->gameData->semaphores[$key] = $semaphore;
    }

    /**
     * Triggers a certain event
     */
    #[Override]
    public function triggerEvent(object $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }

    private function executeCallback(ModuleEnum $module, GameRequest $gameRequest): void
    {
        $actions = $this->controllerDiscovery->getControllers($module, false);

        foreach ($actions as $actionIdentifier => $controller) {
            if (!request::has($actionIdentifier)) {
                continue;
            }

            $gameRequest->setAction($actionIdentifier);

            if ($this->accessCheck->checkUserAccess($controller, $this)) {
                $controller->handle($this);
                $this->entityManager->flush();
            }
            break;
        }
    }

    private function executeView(ModuleEnum $module, GameRequest $gameRequest): void
    {
        $viewFromContext = $this->getViewContext(ViewContextTypeEnum::VIEW);

        $views = $this->controllerDiscovery->getControllers($module, true);

        foreach ($views as $viewIdentifier => $controller) {
            if (
                request::has($viewIdentifier)
                || $viewIdentifier === $viewFromContext
            ) {
                $gameRequest->setView($viewIdentifier);

                if ($this->accessCheck->checkUserAccess($controller, $this)) {
                    $this->handleView($controller);
                    return;
                }
                break;
            }
        }

        $view = $views[self::DEFAULT_VIEW] ?? null;

        if (
            $view !== null
            && $this->accessCheck->checkUserAccess($view, $this)
        ) {
            $this->handleView($view);
        }
    }

    private function handleView(ControllerInterface $view): void
    {
        $view->handle($this);

        if ($view instanceof ViewWithTutorialInterface) {
            $this->tutorialProvider->setTemplateVariables(
                $view->getViewContext(),
                $this
            );
        }

        $this->entityManager->flush();
    }

    #[Override]
    public function getGameStats(): array
    {
        if ($this->gameData->gameStats === null) {
            $this->gameData->gameStats = [
                'currentTurn' => $this->getCurrentRound()->getTurn(),
                'player' => $this->userRepository->getActiveAmount(),
                'playeronline' => $this->userRepository->getActiveAmountRecentlyOnline(time() - 300),
                'gameState' => $this->getGameState(),
                'gameStateTextual' => GameEnum::gameStateTypeToDescription($this->getGameState())
            ];
        }
        return $this->gameData->gameStats;
    }

    #[Override]
    public function resetGameData(): void
    {
        $this->gameData = new GameData();
    }

    /**
     * @return array{executionTime: float|string, memoryPeakUsage: float|string}
     */
    #[Override]
    public function getBenchmarkResult(): array
    {
        $this->benchmark->end();

        return $this->benchmarkResult->getResult($this->benchmark);
    }
}
