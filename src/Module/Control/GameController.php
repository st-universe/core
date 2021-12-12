<?php

namespace Stu\Module\Control;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Exception\SessionInvalidException;
use Noodlehaus\ConfigInterface;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Exception\MaintenanceGameStateException;
use Stu\Exception\RelocationGameStateException;
use Stu\Exception\SemaphoreException;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\TickGameStateException;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalPageInterface;
use Stu\Module\Tal\TalStatusBar;
use Stu\Orm\Entity\GameConfigInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Exception\StuException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Ubench;

final class GameController implements GameControllerInterface
{
    public const DEFAULT_VIEW = 'DEFAULT_VIEW';

    private SessionInterface $session;

    private SessionStringRepositoryInterface $sessionStringRepository;

    private TalPageInterface $talPage;

    private DatabaseUserRepositoryInterface $databaseUserRepository;

    private ConfigInterface $config;

    private GameTurnRepositoryInterface $gameTurnRepository;

    private ResearchedRepositoryInterface $researchedRepository;

    private GameConfigRepositoryInterface $gameConfigRepository;

    private EntityManagerInterface $entityManager;

    private PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    private Ubench $benchmark;

    private CreateDatabaseEntryInterface $createDatabaseEntry;

    private LoggerUtilInterface $loggerUtil;

    private array $gameInformations = [];

    private array $siteNavigation = [];

    private string $pagetitle = '';

    private string $macro = '';

    private array $execjs = [];

    private ?GameTurnInterface $currentRound = null;

    private array $achievements = [];

    private ?int $playercount = null;

    private array $viewContext = [];

    private ?array $gameStats = null;

    private string $loginError = '';

    private $semaphores = [];

    public function __construct(
        SessionInterface $session,
        SessionStringRepositoryInterface $sessionStringRepository,
        TalPageInterface $talPage,
        DatabaseUserRepositoryInterface $databaseUserRepository,
        ConfigInterface $config,
        GameTurnRepositoryInterface $gameTurnRepository,
        ResearchedRepositoryInterface $researchedRepository,
        GameConfigRepositoryInterface $gameConfigRepository,
        EntityManagerInterface $entityManager,
        PrivateMessageFolderRepositoryInterface $privateMessageFolderRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository,
        Ubench $benchmark,
        CreateDatabaseEntryInterface $createDatabaseEntry,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->session = $session;
        $this->sessionStringRepository = $sessionStringRepository;
        $this->talPage = $talPage;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->config = $config;
        $this->gameTurnRepository = $gameTurnRepository;
        $this->researchedRepository = $researchedRepository;
        $this->gameConfigRepository = $gameConfigRepository;
        $this->entityManager = $entityManager;
        $this->privateMessageFolderRepository = $privateMessageFolderRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
        $this->benchmark = $benchmark;
        $this->createDatabaseEntry = $createDatabaseEntry;
        $this->loggerUtil = $loggerUtil;

        $this->loggerUtil->init();
    }

    public function setView(string $view, array $viewContext = []): void
    {
        request::setVar($view, 1);

        $this->viewContext = $viewContext;
    }

    public function getViewContext(): array
    {
        return $this->viewContext;
    }

    public function getGameState(): int
    {
        return (int) $this->getGameConfig()[GameEnum::CONFIG_GAMESTATE]->getValue();
    }

    public function setTemplateFile(string $tpl): void
    {
        $this->talPage->setTemplate($tpl);
    }

    public function setMacro($macro): void
    {
        $this->macro = $macro;
    }

    public function showMacro($macro): void
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->macro = $macro;
    }

    public function getMacro(): string
    {
        return $this->macro;
    }

    public function addInformationfWithLink(string $text, string $link, ...$args): void
    {
        $this->addInformationWithLink(vsprintf(
            $text,
            $args
        ), $link);
    }

    public function addInformationf(string $text, ...$args): void
    {
        $this->addInformation(vsprintf(
            $text,
            $args
        ));
    }

    public function addInformationWithLink(string $msg, string $link, bool $override = false): void
    {
        if ($override) {
            $this->gameInformations = array();
        }
        $notification = new Notification();
        $notification->setText($msg);
        $notification->setLink($link);

        $this->gameInformations[] = $notification;
    }

    public function addInformation(string $msg, bool $override = false): void
    {
        if ($override) {
            $this->gameInformations = array();
        }
        $notification = new Notification();
        $notification->setText($msg);

        $this->gameInformations[] = $notification;
    }

    public function addInformationMerge(array $info): void
    {
        $notificationArray = array();
        foreach ($info as $value) {
            $notification = new Notification();
            $notification->setText($value);
            $notificationArray[] = $notification;
        }

        $this->gameInformations = array_merge($notificationArray, $this->getInformation());
    }

    public function addInformationMergeDown(array $info): void
    {
        $notificationArray = array();
        foreach ($info as $value) {
            $notification = new Notification();
            $notification->setText($value);
            $notificationArray[] = $notification;
        }

        $this->gameInformations = array_merge($this->getInformation(), $notificationArray);
    }

    public function getInformation(): array
    {
        return $this->gameInformations;
    }

    public function sendInformation(
        $recipient_id,
        $sender_id = GameEnum::USER_NOONE,
        $category_id = PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
    ): void {

        $textOnlyArray = array();
        foreach ($this->getInformation() as $value) {
            $textOnlyArray[] = $value->getText();
        }

        $this->privateMessageSender->send(
            (int) $sender_id,
            (int) $recipient_id,
            join('<br />', $textOnlyArray),
            $category_id
        );
    }

    public function setTemplateVar(string $key, $variable): void
    {
        $this->talPage->setVar($key, $variable);
    }

    private function render(): void
    {
        $user = $this->getUser();

        $this->talPage->setVar('THIS', $this);
        $this->talPage->setVar('USER', $user);

        if ($user !== null) {
            $userId = $user->getId();

            $pmFolder = [
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM
            ];
            $folder = [];

            foreach ($pmFolder as $specialId) {
                if (
                    $specialId === PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION
                    && !$user->hasStationsNavigation()
                ) {
                    continue;
                }
                $folder[$specialId] = $this->privateMessageFolderRepository->getByUserAndSpecial($userId, $specialId);
            }

            $researchStatusBar = '';
            $currentResearch = $this->researchedRepository->getCurrentResearch($user->getId());

            if ($currentResearch !== null) {
                $researchStatusBar = (new TalStatusBar())
                    ->setColor(StatusBarColorEnum::STATUSBAR_BLUE)
                    ->setLabel(_('Forschung'))
                    ->setMaxValue($currentResearch->getResearch()->getPoints())
                    ->setValue($currentResearch->getResearch()->getPoints() - $currentResearch->getActive())
                    ->setSizeModifier(2)
                    ->render();
            }

            $this->talPage->setVar('CURRENT_RESEARCH', $currentResearch);
            $this->talPage->setVar('CURRENT_RESEARCH_STATUS', $researchStatusBar);
            $this->talPage->setVar('PM_NAVLET', $folder);
            $this->talPage->setVar('GAME_VERSION', $this->config->get('game.version'));
        }

        $result = $this->talPage->parse();

        ob_start();
        echo $result;
        ob_end_flush();
    }

    public function getUser(): ?UserInterface
    {
        return $this->session->getUser();
    }

    public function getPlayerCount(): int
    {
        if ($this->playercount === null) {
            return $this->userRepository->getActiveAmount();
        }
        return $this->playercount;
    }

    private $gameConfig = null;

    /**
     * @return GameConfigInterface[]
     */
    public function getGameConfig(): array
    {
        if ($this->gameConfig === null) {
            $this->gameConfig = [];

            /** @var GameConfigInterface $item */
            foreach ($this->gameConfigRepository->findAll() as $item) {
                $this->gameConfig[$item->getOption()] = $item;
            }
        }
        return $this->gameConfig;
    }

    public function getUniqId(): string
    {
        return uniqid();
    }

    public function appendNavigationPart(
        string $url,
        string $title
    ): void {
        $this->siteNavigation[$url] = $title;
    }

    public function getNavigation(): array
    {
        return $this->siteNavigation;
    }

    public function getPageTitle(): string
    {
        return $this->pagetitle;
    }

    public function setPageTitle(string $title): void
    {
        $this->pagetitle = $title;
    }

    public function getQueryCount(): int
    {
        return 666;
    }

    public function getExecuteJS(): array
    {
        return $this->execjs;
    }

    public function addExecuteJS(string $value): void
    {
        $this->execjs[] = $value;
    }

    public function redirectTo(string $href): void
    {
        $this->entityManager->commit();;
        header('Location: ' . $href);
        exit;
    }

    public function getCurrentRound(): GameTurnInterface
    {
        if ($this->currentRound === null) {
            $this->currentRound = $this->gameTurnRepository->getCurrent();
        }
        return $this->currentRound;
    }

    public function isDebugMode(): bool
    {
        return in_array($this->getUser()->getId(), [101]) && $this->config->get('debug.debug_mode');
    }

    public function getJavascriptPath(): string
    {
        return sprintf(
            '/version_%d/static',
            $this->config->get('game.version')
        );
    }

    public function checkDatabaseItem($databaseEntryId): void
    {
        $userId = $this->getUser()->getId();

        if ($databaseEntryId > 0 && $this->databaseUserRepository->exists($userId, $databaseEntryId) === false) {
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

    public function getAchievements(): array
    {
        return $this->achievements;
    }

    public function getSessionString(): string
    {
        $string = bin2hex(random_bytes(15));

        $sessionStringEntry = $this->sessionStringRepository->prototype();
        $sessionStringEntry->setUser($this->getUser());
        $sessionStringEntry->setDate(new DateTimeImmutable());
        $sessionStringEntry->setSessionString($string);

        $this->sessionStringRepository->save($sessionStringEntry);

        return $string;
    }

    public function sessionAndAdminCheck(): void
    {
        $this->session->createSession(true);

        if (!$this->getUser()->isAdmin()) {
            header('Location: /');
        }
    }

    public function main(array $actions, array $views, bool $session_check = true, bool $admin_check = false): void
    {
        try {
            $this->session->createSession($session_check);

            if ($session_check === false) {
                $this->session->checkLoginCookie();
            }

            if ($admin_check === true) {
                if (!$this->getUser()->isAdmin()) {
                    header('Location: /');
                }
            }

            if ($this->getUser() !== null) {
                $gameState = $this->getGameState();
                if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_TICK) {
                    throw new TickGameStateException();
                }

                if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE) {
                    throw new MaintenanceGameStateException();
                }

                if ($gameState === GameEnum::CONFIG_GAMESTATE_VALUE_RELOCATION) {
                    throw new RelocationGameStateException();
                }
            }
            $this->executeCallback($actions);
            $this->executeView($views);
        } catch (SessionInvalidException $e) {
            session_destroy();

            if (request::isAjaxRequest()) {
                header('HTTP/1.0 400');
            } else {
                header('Location: /');
            }
            return;
        } catch (LoginException $e) {
            $this->loginError = $e->getMessage();

            $this->executeView($views);
        } catch (TickGameStateException $e) {
            $this->setPageTitle(_('Rundenwechsel aktiv'));
            $this->setTemplateFile('html/tick.xhtml');

            $this->talPage->setVar('THIS', $this);
        } catch (MaintenanceGameStateException $e) {
            $this->setPageTitle(_('Wartungsmodus'));
            $this->setTemplateFile('html/maintenance.xhtml');

            $this->talPage->setVar('THIS', $this);
        } catch (RelocationGameStateException $e) {
            $this->setPageTitle(_('Umzugsmodus'));
            $this->setTemplateFile('html/relocation.xhtml');

            $this->talPage->setVar('THIS', $this);
        } catch (ShipDoesNotExistException $e) {
            $this->addInformation(_('Dieses Schiff existiert nicht!'));
            $this->setTemplateFile('html/ship.xhtml');
        } catch (ShipIsDestroyedException $e) {
            $this->addInformation('Dieses Schiff wurde zerstört!');
            $this->setTemplateFile('html/ship.xhtml');
        } catch (UnallowedUplinkOperation $e) {
            $this->addInformation('Diese Aktion ist per Uplink nicht möglich!');
            $this->setTemplateFile('html/ship.xhtml');
        } catch (StuException $e) {
            $this->releaseAndRemoveSemaphores();
            throw $e;
        } catch (\Throwable $e) {
            $this->releaseAndRemoveSemaphores();
            //if ($this->config->get('debug.debug_mode') === true) {
            if (true === true) {
                throw $e;
            }
            $this->setTemplateFile('html/error.html');
        }

        $this->releaseAndRemoveSemaphores();
        $this->render();
    }

    public function isSemaphoreAlreadyAcquired(int $key): bool
    {
        return array_key_exists($key, $this->semaphores);
    }

    public function addSemaphore(int $key, $semaphore): void
    {
        $this->semaphores[$key] = $semaphore;
    }

    private function releaseAndRemoveSemaphores(): void
    {
        if (!empty($this->semaphores)) {
            $userId = $this->getUser()->getId();

            if ($userId === 999999) {
                $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
            }

            foreach ($this->semaphores as $key => $sema) {
                $this->loggerUtil->log(sprintf('       releasing %d, userId: %d', $key, $userId));
                if (!sem_release($sema)) {
                    throw new SemaphoreException("Error releasing Semaphore!");
                }

                if (!sem_remove($sema)) {
                    throw new SemaphoreException("Error removing Semaphore!");
                }
            }

            $this->loggerUtil->init();
        }
    }

    private function executeCallback(array $actions): void
    {
        foreach ($actions as $request_key => $config) {
            if (request::indString($request_key)) {
                if ($config->performSessionCheck() === true && !request::isPost()) {
                    if (!$this->sessionStringRepository->isValid(
                        (string)request::indString('sstr'),
                        $this->getUser()->getId()
                    )) {
                        return;
                    }
                }
                $config->handle($this);
                $this->entityManager->flush();

                return;
            }
        }
    }

    private function executeView(array $views): void
    {
        foreach ($views as $request_key => $config) {

            if (request::indString($request_key)) {
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

    public function getGameStats(): array
    {
        if ($this->gameStats === null) {
            $this->gameStats = [
                'turn' => $this->getCurrentRound(),
                'player' => $this->getPlayerCount(),
                'playeronline' => $this->userRepository->getActiveAmountRecentlyOnline(time() - 300),
            ];
        }
        return $this->gameStats;
    }

    public function getGameStateTextual(): string
    {
        switch ($this->getGameState()) {
            case GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE:
                return _('Online');
            case GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE:
                return _('Wartung');
            case GameEnum::CONFIG_GAMESTATE_VALUE_RELOCATION:
                return _('Umzug');
            case GameEnum::CONFIG_GAMESTATE_VALUE_TICK:
                return _('Tick');
        }
        return '';
    }

    public function getLoginError(): string
    {
        return $this->loginError;
    }

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

    public function getContactlistModes(): array
    {
        return [
            ContactListModeEnum::CONTACT_FRIEND => [
                'mode' => ContactListModeEnum::CONTACT_FRIEND, 'name' => _('Freund')
            ],
            ContactListModeEnum::CONTACT_ENEMY => [
                'mode' => ContactListModeEnum::CONTACT_ENEMY, 'name' => _('Feind')
            ],
            ContactListModeEnum::CONTACT_NEUTRAL => [
                'mode' => ContactListModeEnum::CONTACT_NEUTRAL, 'name' => _('Neutral')
            ],
        ];
    }
}
