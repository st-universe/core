<?php

namespace Stu\Module\Control;

use BadMethodCallException;
use Override;
use request;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Exception\AccessViolationException;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Session\SessionStringFactoryInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionLoginInterface;
use Stu\Lib\UserLockedException;
use Stu\Lib\UuidGeneratorInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Component\CallbackExecution;
use Stu\Module\Control\Component\ViewExecution;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Control\Router\FallbackRouteException;
use Stu\Module\Control\Router\FallbackRouterInterface;
use Stu\Module\Game\Lib\GameSetupInterface;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\GameRequestRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Ubench;

final class GameController implements GameControllerInterface
{
    public const string DEFAULT_VIEW = 'DEFAULT_VIEW';

    private const string REDIRECT_TO_DOMAIN_ROOT = 'Location: /';

    private GameData $gameData;

    public function __construct(
        private readonly SessionInterface $session,
        private readonly SessionLoginInterface $sessionLogin,
        private readonly CallbackExecution $callbackExecution,
        private readonly ViewExecution $viewExecution,
        private readonly TwigPageInterface $twigPage,
        private readonly StuConfigInterface $stuConfig,
        private readonly GameTurnRepositoryInterface $gameTurnRepository,
        private readonly ComponentSetupInterface $componentSetup,
        private readonly Ubench $benchmark,
        private readonly GameRequestRepositoryInterface $gameRequestRepository,
        private readonly GameTwigRendererInterface $gameTwigRenderer,
        private readonly FallbackRouterInterface $fallbackRouter,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly GameRequestSaverInterface $gameRequestSaver,
        private readonly GameSetupInterface $gameSetup,
        private readonly GameStateInterface $gameState,
        private readonly JavascriptExecutionInterface $javascriptExecution,
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
    public function getGameData(): GameData
    {
        return $this->gameData;
    }

    #[Override]
    public function getInfo(): InformationWrapper
    {
        return $this->gameData->gameInformations;
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
    public function setPageTitle(string $title): void
    {
        $this->gameData->pagetitle = $title;
    }

    #[Override]
    public function addExecuteJS(string $value, JavascriptExecutionTypeEnum $when = JavascriptExecutionTypeEnum::BEFORE_RENDER): void
    {
        $this->javascriptExecution->addExecuteJS($value, $when);
    }

    #[Override]
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
            header(self::REDIRECT_TO_DOMAIN_ROOT);
        }
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
                header(self::REDIRECT_TO_DOMAIN_ROOT);
                exit;
            }

            if ($module === ModuleEnum::ADMIN && !$this->isAdmin()) {
                header(self::REDIRECT_TO_DOMAIN_ROOT);
                exit;
            }

            $this->checkUserLock($gameRequest);
            $this->gameState->checkGameState($this->isAdmin());

            $this->callbackExecution->execute($module, $this);
            $this->viewExecution->execute($module, $this);
        } catch (SessionInvalidException) {
            session_destroy();

            if (request::isAjaxRequest()) {
                header('HTTP/1.0 400');
            } else {
                header(self::REDIRECT_TO_DOMAIN_ROOT);
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

        $this->componentSetup->setup($this);

        $this->render();

        $this->gameRequestSaver->save($gameRequest);
    }

    private function render(): void
    {
        $user = $this->hasUser()
            ? $this->getUser()
            : null;

        $startTime = hrtime(true);
        $renderResult = $this->gameTwigRenderer->render($this, $user);
        $renderMs = hrtime(true) - $startTime;

        ob_start();
        echo $renderResult;
        ob_end_flush();

        // SAVE META DATA
        $this->getGameRequest()->setRenderMs((int)$renderMs / 1_000_000);
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
    public function resetGameData(): void
    {
        $this->gameData = new GameData();
    }

    #[Override]
    public function getBenchmarkResult(): array
    {
        $this->benchmark->end();

        return $this->benchmarkResult->getResult($this->benchmark);
    }
}
