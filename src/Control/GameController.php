<?php

namespace Stu\Control;

use Colony;
use DateTimeImmutable;
use GameConfig;
use GameTurn;
use GameTurnData;
use Noodlehaus\ConfigInterface;
use PM;
use PMCategory;
use request;
use Stu\Lib\DbInterface;
use Stu\Lib\SessionInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use UserData;

final class GameController implements GameControllerInterface
{
    public const DEFAULT_VIEW = 'DEFAULT_VIEW';

    private $session;

    private $sessionStringRepository;

    private $talPage;

    private $databaseUserRepository;

    private $config;

    private $db;

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
        DbInterface $db
    ) {
        $this->session = $session;
        $this->sessionStringRepository = $sessionStringRepository;
        $this->talPage = $talPage;
        $this->databaseUserRepository = $databaseUserRepository;
        $this->config = $config;
        $this->db = $db;
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
        if ($this->getGameState() == CONFIG_GAMESTATE_VALUE_TICK) {
            $this->setPageTitle("Rundenwechsel aktiv");
            $this->setTemplateFile('html/tick.xhtml');
        }
        if ($this->getGameState() == CONFIG_GAMESTATE_VALUE_MAINTENANCE) {
            $this->setPageTitle("Wartungsmodus");
            $this->setTemplateFile('html/maintenance.xhtml');
        }
    }

    public function getGameState(): string
    {
        return $this->getGameConfig()[CONFIG_GAMESTATE]->getValue();
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

    public function getMemoryUsage()
    {
        return round((memory_get_peak_usage() / 1024) / 1024, 3);
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

    public function sendInformation($recipient_id, $sender_id = USER_NOONE, $category_id = PM_SPECIAL_MAIN)
    {
        PM::sendPM($sender_id, $recipient_id, join('<br />', $this->getInformation()), $category_id);
    }

    public function setTemplateVar(string $key, $variable): void
    {
        $this->talPage->setVar($key, $variable);
    }

    private function render(): void
    {
        $user = $this->getUser();

        if ($this->getGameState() != CONFIG_GAMESTATE_VALUE_ONLINE) {
            $this->maintenanceView();
        }
        $this->talPage->setVar('THIS', $this);
        $this->talPage->setVar('GFX', GFX_PATH);
        $this->talPage->setVar('USER', $user);

        if ($user !== null) {
            $this->talPage->setVar('PM_NAVLET', PMCategory::getNavletCategories($user->getId()));
        }

        $this->talPage->parse();
    }

    public function getUser(): ?UserData
    {
        return $this->session->getUser();
    }

    public function getBenchmark(): float
    {
        global $benchmark_start;

        $start = explode(' ', $benchmark_start);
        $s_timer = $start[1] + $start[0];
        $end = explode(' ', microtime());
        $e_timer = $end[1] + $end[0];
        return round($e_timer - $s_timer, 6);;
    }

    public function getPlayerCount(): int
    {
        if ($this->playercount === null) {
            $this->playercount = (int) $this->db->query("SELECT COUNT(*) FROM stu_user WHERE id>100", 1);
        }
        return $this->playercount;
    }

    private $gameConfig = null;

    /**
     * @return GameConfig[]
     */
    public function getGameConfig(): array
    {
        if ($this->gameConfig === null) {
            $this->gameConfig = GameConfig::getObjectsBy();
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
        return (int) $this->db->getQueryCount();
    }

    public function getDebugNotices(): array
    {
        return get_debug_error()->getDebugNotices();
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
        $this->db->commitTransaction();
        header('Location: ' . $href);
        exit;
    }

    public function getCurrentRound(): GameTurnData
    {
        if ($this->currentRound === null) {
            $this->currentRound = GameTurn::getCurrentTurn();
        }
        return $this->currentRound;
    }

    public function isDebugMode(): bool
    {
        return $this->config->get('debug.debug_mode');
    }

    public function getJavascriptPath(): string
    {
        return sprintf('version_%s', $this->getGameVersion());
    }

    public function getPlanetColonyLimit(): int
    {
        return (int) $this->db->query(
            'SELECT SUM(upper_planetlimit)+1 FROM stu_research WHERE id IN (SELECT research_id FROM stu_researched WHERE user_id=' . $this->getUser()->getId() . ' ANd aktiv=0)',
            1
        );
    }

    public function getMoonColonyLimit(): int
    {
        return (int) $this->db->query(
            'SELECT SUM(upper_moonlimit) FROM stu_research WHERE id IN (SELECT research_id FROM stu_researched WHERE user_id=' . $this->getUser()->getId() . ' ANd aktiv=0)',
            1
        );
    }

    public function getPlanetColonyCount(): int
    {
        return (int) Colony::countInstances('WHERE user_id=' . $this->getUser()->getId() . ' AND colonies_classes_id IN (SELECT id FROM stu_colonies_classes WHERE is_moon=0)');
    }

    public function getMoonColonyCount(): int
    {
        return (int) Colony::countInstances('WHERE user_id=' . $this->getUser()->getId() . ' AND colonies_classes_id IN (SELECT id FROM stu_colonies_classes WHERE is_moon=1)');
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
        $sessionStringEntry->setUserId($this->getUser()->getId());
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
        return true;
    }

    public function getGameStats(): array
    {
        if ($this->gameStats === null) {
            $this->gameStats = [
                'turn' => $this->getCurrentRound(),
                'player' => $this->getPlayerCount(),
                'playeronline' => $this->db->query("SELECT COUNT(*) FROM stu_user WHERE id>100 AND lastaction>" . time() . "-300",
                    1),
            ];
        }
        return $this->gameStats;
    }

    public function getGameStateTextual()
    {
        switch ($this->getGameState()) {
            case CONFIG_GAMESTATE_VALUE_ONLINE:
                return _('Online');
            case CONFIG_GAMESTATE_VALUE_MAINTENANCE:
                return _('Wartung');
            case CONFIG_GAMESTATE_VALUE_TICK:
                return _('Tick');
        }
    }

    public function setLoginError(string $error): void
    {
        $this->loginError = $error;
    }

    public function getLoginError(): string
    {
        return $this->loginError;
    }
}
