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
use Stu\Exception\AccessViolation;
use Stu\Exception\EntityLockedException;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\ResetGameStateException;
use Stu\Exception\SanityCheckException;
use Stu\Exception\SessionInvalidException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\LoginException;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Lib\SessionInterface;
use Stu\Lib\UserLockedException;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Game\Lib\TutorialProvider;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\GameConfigInterface;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use SysvSemaphore;
use Ubench;

final class GameController implements GameControllerInterface
{
    public const string DEFAULT_VIEW = 'DEFAULT_VIEW';

    private GameData $gameData;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ControllerDiscovery $controllerDiscovery,
        private AccessCheckInterface $accessCheck,
        private SessionInterface $session,
        private SessionStringRepositoryInterface $sessionStringRepository,
        private TwigPageInterface $twigPage,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private StuConfigInterface $stuConfig,
        private GameTurnRepositoryInterface $gameTurnRepository,
        private GameConfigRepositoryInterface $gameConfigRepository,
        private EntityManagerInterface $entityManager,
        private UserRepositoryInterface $userRepository,
        private ComponentSetupInterface $componentSetup,
        private Ubench $benchmark,
        private CreateDatabaseEntryInterface $createDatabaseEntry,
        private GameRequestRepositoryInterface $gameRequestRepository,
        private GameTwigRendererInterface $gameTwigRenderer,
        private UuidGeneratorInterface $uuidGenerator,
        private EventDispatcherInterface $eventDispatcher,
        private GameRequestSaverInterface $gameRequestSaver,
        private GameSetupInterface $gameSetup,
        private TutorialProvider $tutorialProvider,
        private SessionStringFactoryInterface $sessionStringFactory,
        private BenchmarkResultInterface $benchmarkResult,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        //$this->loggerUtil->init('game', LoggerEnum::LEVEL_ERROR);

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
        $this->loggerUtil->log(sprintf('setTemplateFile: %s', $template));

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
        $this->loggerUtil->log(sprintf('showMacro: %s', $macro));

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
    public function getUser(): UserInterface
    {
        $user = $this->session->getUser();

        if ($user === null) {
            throw new AccessViolation('User not set');
        }
        return $user;
    }

    #[Override]
    public function hasUser(): bool
    {
        return $this->session->getUser() !== null;
    }

    /**
     * @return array<int, GameConfigInterface>
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
                $this->loggerUtil->log(sprintf('before: %s', $value));
                break;
            case GameEnum::JS_EXECUTION_AFTER_RENDER:
                $this->gameData->execjs[$when][] = $value;
                $this->loggerUtil->log(sprintf('after: %s', $value));
                break;
            case GameEnum::JS_EXECUTION_AJAX_UPDATE:
                $this->gameData->execjs[$when][] = $value;
                $this->loggerUtil->log(sprintf('update: %s', $value));
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
    public function getCurrentRound(): GameTurnInterface
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
    public function getGameRequest(): GameRequestInterface
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
                $this->session->checkLoginCookie();
            }

            if ($module === ModuleEnum::NPC) {
                if (!$this->isNpc() && !$this->isAdmin()) {
                    header('Location: /');
                    exit;
                }
            }

            if ($module === ModuleEnum::ADMIN) {
                if (!$this->isAdmin()) {
                    header('Location: /');
                    exit;
                }
            }

            $this->checkUserAndGameState($gameRequest);

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
        } catch (LoginException $e) {
            $this->setTemplateVar('LOGIN_ERROR', $e->getMessage());
            $this->setTemplateFile('html/index/index.twig');
        } catch (UserLockedException $e) {
            $this->setTemplateFile('html/index/accountLocked.twig');
            $this->setTemplateVar('LOGIN_ERROR', $e->getMessage());
            $this->setTemplateVar('REASON', $e->getDetails());
        } catch (AccountNotVerifiedException $e) {
            $this->setTemplateFile('html/index/smsVerification.twig');
            if ($e->getMessage() !== '') {
                $this->setTemplateVar('REASON', $e->getMessage());
            }
        } catch (MaintenanceGameStateException) {
            $this->setPageTitle('Wartungsmodus');
            $this->setTemplateFile('html/index/maintenance.twig');
        } catch (ResetGameStateException) {
            $this->setPageTitle('Resetmodus');
            $this->setTemplateFile('html/index/gameReset.twig');
        } catch (RelocationGameStateException) {
            $this->setPageTitle('Umzugsmodus');
            $this->setTemplateFile('html/index/relocation.twig');
        } catch (SpacecraftDoesNotExistException $e) {
            $this->addInformation($e->getMessage());
            $this->setViewTemplate('html/empty.twig');
        } catch (ItemNotFoundException) {
            $this->addInformation('Das angeforderte Item wurde nicht gefunden');
            $this->setViewTemplate('html/empty.twig');
        } catch (UnallowedUplinkOperation) {
            $this->addInformation('Diese Aktion ist per Uplink nicht möglich!');

            if (request::isAjaxRequest() && !request::has('switch')) {
                $this->setMacroInAjaxWindow('html/systeminformation.twig');
            } else {
                $this->setViewTemplate('html/empty.twig');
            }
        }

        $isTemplateSet = $this->twigPage->isTemplateSet();

        if (!$isTemplateSet) {
            $this->loggerUtil->init('template', LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log(sprintf('NO TEMPLATE FILE SPECIFIED, Method: %s', request::isPost() ? 'POST' : 'GET'));
            $this->loggerUtil->log(print_r(request::isPost() ? request::postvars() : request::getvars(), true));
            $this->loggerUtil->init('stu');
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

    private function checkUserAndGameState(GameRequestInterface $gameRequest): void
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
            if (!request::postString('smscode') && $this->getUser()->getState() === UserEnum::USER_STATE_SMS_VERIFICATION) {
                throw new AccountNotVerifiedException();
            }
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

    private function executeCallback(ModuleEnum $module, GameRequestInterface $gameRequest): void
    {
        $actions = $this->controllerDiscovery->getControllers($module, false);

        foreach ($actions as $actionIdentifier => $controller) {
            if (request::has($actionIdentifier)) {
                $gameRequest->setAction($actionIdentifier);

                if (
                    $controller instanceof ActionControllerInterface
                    && $controller->performSessionCheck() === true
                    && !request::isPost() && !$this->sessionStringRepository->isValid(
                        (string)request::indString('sstr'),
                        $this->getUser()->getId()
                    )
                ) {
                    return;
                }

                if ($this->accessCheck->checkUserAccess($controller, $this)) {
                    $controller->handle($this);
                    $this->entityManager->flush();
                }
            }
        }
    }

    private function executeView(ModuleEnum $module, GameRequestInterface $gameRequest): void
    {
        $viewFromContext = $this->getViewContext(ViewContextTypeEnum::VIEW);

        $views = $this->controllerDiscovery->getControllers($module, true);

        foreach ($views as $viewIdentifier => $controller) {
            if (
                request::indString($viewIdentifier) !== false
                || $viewIdentifier === $viewFromContext
            ) {
                $gameRequest->setView($viewIdentifier);

                if ($this->accessCheck->checkUserAccess($controller, $this)) {
                    $this->handleView($controller);
                    return;
                }
            }
        }

        $view = $views[self::DEFAULT_VIEW] ?? null;

        if ($view !== null) {
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
                'gameStateTextual' => $this->getGameStateTextual()
            ];
        }
        return $this->gameData->gameStats;
    }

    #[Override]
    public function getGameStateTextual(): string
    {
        return GameEnum::gameStateTypeToDescription($this->getGameState());
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
        $this->loggerUtil->log(sprintf('getBenchmarkResult, timestamp: %F', microtime(true)));
        $this->benchmark->end();

        return $this->benchmarkResult->getResult($this->benchmark);
    }
}
