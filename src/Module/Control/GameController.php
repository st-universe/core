<?php

namespace Stu\Module\Control;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Noodlehaus\ConfigInterface;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Lib\SessionInterface;
use Stu\Module\Communication\Lib\ContactListModeEnum;
use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Entity\GameConfigInterface;
use Stu\Orm\Entity\GameTurnInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\GameConfigRepositoryInterface;
use Stu\Orm\Repository\GameTurnRepositoryInterface;
use Stu\Orm\Repository\PrivateMessageFolderRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Ubench;

final class GameController implements GameControllerInterface
{
    public const DEFAULT_VIEW = 'DEFAULT_VIEW';

    private $session;

    private $sessionStringRepository;

    private $talPage;

    private $databaseUserRepository;

    private $config;

    private $gameTurnRepository;

    private $researchedRepository;

    private $gameConfigRepository;

    private $entityManager;

    private $privateMessageFolderRepository;

    private $privateMessageSender;

    private $colonyRepository;

    private $userRepository;

    private $researchRepository;

    private $benchmark;

    private $gameInformations = [];

    private $siteNavigation = [];

    private $pagetitle = '';

    private $macro = '';

    private $execjs = [];

    private $currentRound;

    private $achievements = [];

    private $playercount;

    private $viewContext = [];

    private $gameStats;

    private $loginError = '';

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
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository,
        ResearchRepositoryInterface $researchRepository,
        Ubench $benchmark
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
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
        $this->researchRepository = $researchRepository;
        $this->benchmark = $benchmark;
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

    private function maintenanceView(): void
    {
        if ($this->getGameState() == GameEnum::CONFIG_GAMESTATE_VALUE_TICK) {
            $this->setPageTitle("Rundenwechsel aktiv");
            $this->setTemplateFile('html/tick.xhtml');
        }
        if ($this->getGameState() == GameEnum::CONFIG_GAMESTATE_VALUE_MAINTENANCE) {
            $this->setPageTitle("Wartungsmodus");
            $this->setTemplateFile('html/maintenance.xhtml');
        }
    }

    public function getGameState(): string
    {
        return $this->getGameConfig()[GameEnum::CONFIG_GAMESTATE]->getValue();
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

    public function addInformationf(string $text, ...$args): void
    {
        $this->addInformation(vsprintf(
            $text,
            $args
        ));
    }

    public function addInformation(string $msg, bool $override = false): void
    {
        if ($override) {
            $this->gameInformations = array();
        }
        $this->gameInformations[] = $msg;
    }

    public function addInformationMerge(array $info): void
    {
        $this->gameInformations = array_merge($info, $this->getInformation());
    }

    public function addInformationMergeDown(array $info): void
    {
        $this->gameInformations = array_merge($this->getInformation(), $info);
    }

    public function getInformation(): array
    {
        return $this->gameInformations;
    }

    public function sendInformation($recipient_id, $sender_id = GameEnum::USER_NOONE, $category_id = PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN
    )
    {
        $this->privateMessageSender->send((int) $sender_id, (int) $recipient_id, join('<br />', $this->getInformation()), $category_id);
    }

    public function setTemplateVar(string $key, $variable): void
    {
        $this->talPage->setVar($key, $variable);
    }

    private function render(): void
    {
        $user = $this->getUser();

        if ($this->getGameState() != GameEnum::CONFIG_GAMESTATE_VALUE_ONLINE) {
            $this->maintenanceView();
        }
        $this->talPage->setVar('THIS', $this);
        $this->talPage->setVar('USER', $user);

        if ($user !== null) {
            $userId = $user->getId();

            $pmFolder = [
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_MAIN,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_COLONY,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_TRADE,
            ];
            $folder = [];

            foreach ($pmFolder as $specialId) {
                $folder[$specialId] = $this->privateMessageFolderRepository->getByUserAndSpecial($userId, $specialId);
            }

            $this->talPage->setVar('CURRENT_RESEARCH', $this->researchedRepository->getCurrentResearch((int) $user->getId()));
            $this->talPage->setVar(
                'USER_COLONIES',
                $this->colonyRepository->getOrderedListByUser($user)
            );
            $this->talPage->setVar('PM_NAVLET', $folder);
        }

        $this->talPage->parse();
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

    public function getGameVersion(): string
    {
        return (string) $this->config->get('game.version');
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
        return $this->isAdmin() && $this->config->get('debug.debug_mode');
    }

    public function getJavascriptPath(): string
    {
        return sprintf('version_%s', $this->getGameVersion());
    }

    public function getPlanetColonyLimit(): int
    {
        return $this->researchRepository->getPlanetColonyLimitByUser($this->getUser()->getId());
    }

    public function getMoonColonyLimit(): int
    {
        return $this->researchRepository->getMoonColonyLimitByUser($this->getUser()->getId());
    }

    public function getPlanetColonyCount(): int
    {
        return $this->colonyRepository->getAmountByUser($this->getUser()->getId());
    }

    public function getMoonColonyCount(): int
    {
        return $this->colonyRepository->getAmountByUser($this->getUser()->getId(), true);
    }

    public function isAdmin(): bool
    {
        return $this->getUser()->isAdmin();
    }

    public function checkDatabaseItem($databaseEntryId): void
    {
        $userId = $this->getUser()->getId();

        if ($databaseEntryId > 0 && $this->databaseUserRepository->exists($userId, $databaseEntryId) === false) {
            $this->achievements[] = databaseScan($databaseEntryId, $userId);
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

    public function main(array $actions, array $views, bool $session_check = true): void
    {
        $this->session->createSession($session_check);

        if ($session_check === false) {
            $this->session->checkLoginCookie();
        }

        $this->executeCallback($actions);
        $this->executeView($views);

        $this->render();
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
                return;
            }
        }
    }

    private function executeView(array $views): void
    {
        foreach ($views as $request_key => $config) {

            if (request::indString($request_key)) {
                $config->handle($this);
                return;
            }
        }

        $view = $views[static::DEFAULT_VIEW] ?? null;

        if ($view !== null) {
            $view->handle($this);
        }
    }

    public function isRegistrationPossible(): bool
    {
        return $this->config->get('game.registration_enabled');
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
            case GameEnum::CONFIG_GAMESTATE_VALUE_TICK:
                return _('Tick');
        }
        return '';
    }

    public function setLoginError(string $error): void
    {
        $this->loginError = $error;
    }

    public function getLoginError(): string
    {
        return $this->loginError;
    }

    public function getBenchmarkResult(): array
    {
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
