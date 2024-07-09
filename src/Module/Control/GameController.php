<?php

namespace Stu\Module\Control;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Psr\EventDispatcher\EventDispatcherInterface;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Exception\AccessViolation;
use Stu\Exception\EntityLockedException;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\ResetGameStateException;
use Stu\Exception\SanityCheckException;
use Stu\Exception\SessionInvalidException;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Lib\AccountNotVerifiedException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Lib\UserLockedException;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Exception\ItemNotFoundException;
use Stu\Module\Control\Render\GameTalRendererInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Tal\TalPageInterface;
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
use Throwable;
use Ubench;

final class GameController implements GameControllerInterface
{
    /** @var string */
    public const string DEFAULT_VIEW = 'DEFAULT_VIEW';

    /** @var string */
    private const string GAME_VERSION_DEV = 'dev';

    private LoggerUtilInterface $loggerUtil;

    private bool $isTwig = false;

    private InformationWrapper $gameInformations;

    private ?TargetLink $targetLink = null;

    /** @var array<string, string> */
    private array $siteNavigation = [];

    private string $pagetitle = '';

    private string $macro = '';

    /** @var array<int, array<string>> */
    private array $execjs = [];

    private ?GameTurnInterface $currentRound = null;

    /** @var array<string> */
    private array $achievements = [];

    /** @var array<int, mixed> $viewContext */
    private array $viewContext = [];

    private ?array $gameStats = null;

    private string $loginError = '';

    /** @var array<int, resource> */
    private array $semaphores = [];

    /** @var GameConfigInterface[]|null */
    private ?array $gameConfig = null;

    private ?GameRequestInterface $gameRequest = null;

    public function __construct(
        private SessionInterface $session,
        private SessionStringRepositoryInterface $sessionStringRepository,
        private TalPageInterface $talPage,
        private TwigPageInterface $twigPage,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private StuConfigInterface $stuConfig,
        private GameTurnRepositoryInterface $gameTurnRepository,
        private GameConfigRepositoryInterface $gameConfigRepository,
        private EntityManagerInterface $entityManager,
        private UserRepositoryInterface $userRepository,
        private Ubench $benchmark,
        private CreateDatabaseEntryInterface $createDatabaseEntry,
        private GameRequestRepositoryInterface $gameRequestRepository,
        private GameTalRendererInterface $gameTalRenderer,
        private GameTwigRendererInterface $gameTwigRenderer,
        private UuidGeneratorInterface $uuidGenerator,
        private EventDispatcherInterface $eventDispatcher,
        private GameRequestSaverInterface $gameRequestSaver,
        private GameSetupInterface $gameSetup,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        //$this->loggerUtil->init('game', LoggerEnum::LEVEL_ERROR);
        $this->gameInformations = new InformationWrapper();
    }

    #[Override]
    public function setView(ModuleViewEnum|string $view): void
    {
        if ($view instanceof ModuleViewEnum) {
            $this->setViewContext(ViewContextTypeEnum::MODULE_VIEW, $view);
        } else {
            $this->setViewContext(ViewContextTypeEnum::VIEW, $view);
        }
    }

    #[Override]
    public function getViewContext(ViewContextTypeEnum $type): mixed
    {
        if (!array_key_exists($type->value, $this->viewContext)) {
            return null;
        }

        return $this->viewContext[$type->value];
    }

    #[Override]
    public function setViewContext(ViewContextTypeEnum $type, mixed $value): void
    {
        $this->viewContext[$type->value] = $value;
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

        if (str_ends_with($template, '.twig')) {
            $this->isTwig = true;
            $this->twigPage->setTemplate($template);
        } else {
            $this->isTwig = false;
            $this->talPage->setTemplate($template);
        }
    }

    #[Override]
    public function setMacroAndTemplate(string $macro, string $tpl): void
    {
        $this->macro = $macro;
        $this->setTemplateFile($tpl);
    }

    #[Override]
    public function setMacroInAjaxWindow(string $macro): void
    {
        $this->macro = $macro;

        if (str_ends_with($macro, '.twig')) {
            $this->setTemplateFile('html/ajaxwindow.twig');
        } else {
            $this->setTemplateFile('html/ajaxwindow.xhtml');
        }
    }

    #[Override]
    public function showMacro(string $macro): void
    {
        $this->loggerUtil->log(sprintf('showMacro: %s', $macro));

        $this->macro = $macro;

        if (str_ends_with($macro, '.twig')) {
            $this->setTemplateFile('html/ajaxempty.twig');
        } else {
            $this->setTemplateFile('html/ajaxempty.xhtml');
        }
    }

    #[Override]
    public function getMacro(): string
    {
        return $this->macro;
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
        if ($information !== null) {
            $this->loggerUtil->log(sprintf('addInformation: %s', $information));

            $this->gameInformations->addInformation($information);
        }

        return $this;
    }

    #[Override]
    public function addInformationMerge(array $info): void
    {
        $this->gameInformations->addInformationArray($info, true);
    }

    #[Override]
    public function addInformationMergeDown(array $info): void
    {
        $this->gameInformations->addInformationArray($info);
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
        return $this->gameInformations->getInformations();
    }

    #[Override]
    public function getTargetLink(): ?TargetLink
    {
        return $this->targetLink;
    }

    #[Override]
    public function setTargetLink(TargetLink $targetLink): GameControllerInterface
    {
        $this->targetLink = $targetLink;

        return $this;
    }

    #[Override]
    public function setTemplateVar(string $key, $variable): void
    {
        $this->twigPage->setVar($key, $variable);
        $this->talPage->setVar($key, $variable);
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
    #[Override]
    public function getGameConfig(): array
    {
        if ($this->gameConfig === null) {
            $this->gameConfig = [];

            foreach ($this->gameConfigRepository->findAll() as $item) {
                $this->gameConfig[$item->getOption()] = $item;
            }
        }
        return $this->gameConfig;
    }

    #[Override]
    public function getUniqId(): string
    {
        return uniqid();
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
        $this->siteNavigation[$url] = $title;
    }

    #[Override]
    public function getNavigation(): array
    {
        return $this->siteNavigation;
    }

    #[Override]
    public function getPageTitle(): string
    {
        return $this->pagetitle;
    }

    #[Override]
    public function setPageTitle(string $title): void
    {
        $this->pagetitle = $title;
    }

    #[Override]
    public function getExecuteJS(int $when): ?array
    {
        if (!array_key_exists($when, $this->execjs)) {
            return null;
        }

        return $this->execjs[$when];
    }

    #[Override]
    public function addExecuteJS(string $value, int $when = GameEnum::JS_EXECUTION_BEFORE_RENDER): void
    {
        switch ($when) {
            case GameEnum::JS_EXECUTION_BEFORE_RENDER:
                $this->execjs[$when][] = $value;
                $this->loggerUtil->log(sprintf('before: %s', $value));
                break;
            case GameEnum::JS_EXECUTION_AFTER_RENDER:
                $this->execjs[$when][] = $value;
                $this->loggerUtil->log(sprintf('after: %s', $value));
                break;
            case GameEnum::JS_EXECUTION_AJAX_UPDATE:
                $this->execjs[$when][] = $value;
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
        if ($this->currentRound === null) {
            $this->currentRound = $this->gameTurnRepository->getCurrent();
        }
        return $this->currentRound;
    }

    #[Override]
    public function getJavascriptPath(): string
    {
        $gameVersion = $this->stuConfig->getGameSettings()->getVersion();
        if ($gameVersion === self::GAME_VERSION_DEV) {
            return '/static';
        }

        return sprintf(
            '/version_%s/static',
            $gameVersion
        );
    }

    #[Override]
    public function checkDatabaseItem($databaseEntryId): void
    {
        $userId = $this->getUser()->getId();

        if (
            $databaseEntryId > 0
            && !$this->databaseUserRepository->exists($userId, $databaseEntryId)
        ) {
            $entry = $this->createDatabaseEntry->createDatabaseEntryForUser($this->getUser(), $databaseEntryId);

            if ($entry !== null) {
                $this->achievements[] = sprintf(
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
        return $this->achievements;
    }

    #[Override]
    public function getSessionString(): string
    {
        $string = bin2hex(random_bytes(15));

        $sessionStringEntry = $this->sessionStringRepository->prototype();
        $sessionStringEntry->setUser($this->getUser());
        $sessionStringEntry->setDate(new DateTime());
        $sessionStringEntry->setSessionString($string);

        $this->sessionStringRepository->save($sessionStringEntry);

        return $string;
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
        if ($this->gameRequest === null) {
            $gameRequest = $this->gameRequestRepository->prototype();
            $gameRequest->setTime(time());
            $gameRequest->setTurnId($this->getCurrentRound());
            $gameRequest->setParameterArray(request::isPost() ? request::postvars() : request::getvars());
            $gameRequest->setRequestId($this->uuidGenerator->genV4());

            if ($this->hasUser()) {
                $gameRequest->setUserId($this->getUser());
            }

            $this->gameRequest = $gameRequest;
        }
        return $this->gameRequest;
    }

    #[Override]
    public function main(
        ModuleViewEnum $view,
        array $actions,
        array $views,
        bool $session_check = true,
        bool $admin_check = false,
        bool $npc_check = false,
    ): void {
        $this->setViewContext(ViewContextTypeEnum::MODULE_VIEW, $view);

        $gameRequest = $this->getGameRequest();
        $gameRequest->setModule($view->value);

        try {
            $this->session->createSession($session_check);

            if ($session_check === false) {
                $this->session->checkLoginCookie();
            }

            if (
                $admin_check === true
                && $this->isAdmin() === false
            ) {
                header('Location: /');
            }

            if (
                $npc_check === true
                && $this->isNpc() === false
            ) {
                header('Location: /');
            }

            $this->checkUserAndGameState($gameRequest);

            // log action & view and time they took
            $startTime = hrtime(true);
            try {
                $this->executeCallback($actions, $gameRequest);
            } catch (SanityCheckException $e) {
                $gameRequest->addError($e);
            } catch (EntityLockedException $e) {
                $this->addInformation($e->getMessage());
            }
            $actionMs = hrtime(true) - $startTime;

            $startTime = hrtime(true);
            try {
                $this->executeView($views, $gameRequest);
            } catch (SanityCheckException $e) {
                $gameRequest->addError($e);
            } catch (EntityLockedException $e) {
                $this->addInformation($e->getMessage());
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
            $this->loginError = $e->getMessage();
            $this->setTemplateVar('THIS', $this);
            $this->setTemplateFile('html/index/index.twig');
        } catch (UserLockedException $e) {
            $this->loginError = $e->getMessage();

            $this->setTemplateFile('html/index/accountLocked.twig');
            $this->setTemplateVar('THIS', $this);
            $this->setTemplateVar('REASON', $e->getDetails());
        } catch (AccountNotVerifiedException $e) {
            $this->setTemplateFile('html/index/smsVerification.twig');
            $this->setTemplateVar('THIS', $this);
            if ($e->getMessage() !== '') {
                $this->setTemplateVar('REASON', $e->getMessage());
            }
        } catch (MaintenanceGameStateException) {
            $this->setPageTitle(_('Wartungsmodus'));
            $this->setTemplateFile('html/index/maintenance.twig');

            $this->setTemplateVar('THIS', $this);
        } catch (ResetGameStateException) {
            $this->setPageTitle(_('Resetmodus'));
            $this->setTemplateFile('html/index/gameReset.twig');

            $this->setTemplateVar('THIS', $this);
        } catch (RelocationGameStateException) {
            $this->setPageTitle(_('Umzugsmodus'));
            $this->setTemplateFile('html/index/relocation.twig');

            $this->setTemplateVar('THIS', $this);
        } catch (ShipDoesNotExistException) {
            $this->addInformation(_('Dieses Schiff existiert nicht!'));
            $this->setViewTemplate('html/ship/ship.twig');
        } catch (ShipIsDestroyedException) {
            $this->addInformation('Dieses Schiff wurde zerstört!');
            $this->setViewTemplate('html/ship/ship.twig');
        } catch (ItemNotFoundException) {
            $this->addInformation('Das angeforderte Item wurde nicht gefunden');
            $this->setTemplateFile('html/notFound.twig');
        } catch (UnallowedUplinkOperation) {
            $this->addInformation('Diese Aktion ist per Uplink nicht möglich!');

            if (request::isAjaxRequest()) {
                $this->setMacroInAjaxWindow('html/sitemacros.xhtml/systeminformation');
            } else {
                $this->setViewTemplate('html/ship/ship.twig');
            }
        } catch (Throwable $e) {
            throw $e;
        }

        $isTemplateSet = $this->isTwig ? $this->twigPage->isTemplateSet() : $this->talPage->isTemplateSet();

        if (!$isTemplateSet) {
            $this->loggerUtil->init('tal', LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log(sprintf('NO TEMPLATE FILE SPECIFIED, Method: %s', request::isPost() ? 'POST' : 'GET'));
            $this->loggerUtil->log(print_r(request::isPost() ? request::postvars() : request::getvars(), true));
            $this->loggerUtil->init('stu');
        }

        $user = $this->hasUser()
            ? $this->getUser()
            : null;

        // RENDER!
        $startTime = hrtime(true);

        $renderResult = $this->isTwig ? $this->gameTwigRenderer->render($this, $user, $this->twigPage) : $this->gameTalRenderer->render($this, $user, $this->talPage);

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
            $gameRequest->setUserId($this->getUser());

            if ($this->getUser()->isLocked()) {
                $userLock = $this->getUser()->getUserLock();
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
        return array_key_exists($key, $this->semaphores);
    }

    #[Override]
    public function addSemaphore(int $key, $semaphore): void
    {
        $this->semaphores[$key] = $semaphore;
    }

    /**
     * Triggers a certain event
     */
    #[Override]
    public function triggerEvent(object $event): void
    {
        $this->eventDispatcher->dispatch($event);
    }

    /**
     * @param array<string, ActionControllerInterface> $actions
     */
    private function executeCallback(array $actions, GameRequestInterface $gameRequest): void
    {
        foreach ($actions as $actionIdentifier => $actionController) {
            if (request::has($actionIdentifier)) {
                $gameRequest->setAction($actionIdentifier);

                if ($actionController->performSessionCheck() === true && !request::isPost() && !$this->sessionStringRepository->isValid(
                    (string)request::indString('sstr'),
                    $this->getUser()->getId()
                )) {
                    return;
                }
                $actionController->handle($this);

                $this->entityManager->flush();
            }
        }
    }

    /**
     * @param array<string, ViewControllerInterface> $views
     */
    private function executeView(array $views, GameRequestInterface $gameRequest): void
    {
        $viewFromContext = $this->getViewContext(ViewContextTypeEnum::VIEW);

        foreach ($views as $viewIdentifier => $config) {
            if (
                request::indString($viewIdentifier) !== false
                || $viewIdentifier === $viewFromContext
            ) {
                $gameRequest->setView($viewIdentifier);

                $config->handle($this);
                $this->entityManager->flush();

                return;
            }
        }

        $view = $views[static::DEFAULT_VIEW] ?? null;

        if ($view !== null) {
            $view->handle($this);
            $this->entityManager->flush();
        }
    }

    #[Override]
    public function getGameStats(): array
    {
        if ($this->gameStats === null) {
            $this->gameStats = [
                'currentTurn' => $this->getCurrentRound()->getTurn(),
                'player' => $this->userRepository->getActiveAmount(),
                'playeronline' => $this->userRepository->getActiveAmountRecentlyOnline(time() - 300),
                'gameState' => $this->getGameState(),
                'gameStateTextual' => $this->getGameStateTextual()
            ];
        }
        return $this->gameStats;
    }

    #[Override]
    public function getGameStateTextual(): string
    {
        return GameEnum::gameStateTypeToDescription($this->getGameState());
    }

    #[Override]
    public function getLoginError(): string
    {
        return $this->loginError;
    }

    /**
     * @return array{executionTime: float|string, memoryUsage: float|string, memoryPeakUsage: float|string}
     */
    #[Override]
    public function getBenchmarkResult(): array
    {
        $this->loggerUtil->log(sprintf('getBenchmarkResult, timestamp: %F', microtime(true)));
        $this->benchmark->end();

        return [
            'executionTime' => $this->benchmark->getTime(),
            'memoryUsage' => $this->benchmark->getMemoryUsage(),
            'memoryPeakUsage' => $this->benchmark->getMemoryPeak()
        ];
    }
}
