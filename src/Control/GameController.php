<?php

namespace Stu\Control;

use Colony;
use DateTimeImmutable;
use GameConfig;
use GameTurn;
use InvalidCallbackException;
use PM;
use PMCategory;
use request;
use Stu\Lib\SessionInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use TalPage;
use Tuple;
use UserData;

abstract class GameController implements GameControllerInterface
{
    public const DEFAULT_VIEW = 'DEFAULT_VIEW';

    private $session;

    private $sessionStringRepository;

    private $tpl_file = null;
    private $gameInformations = array();
    private $benchmarkTimer = null;
    private $handleId = 0;
    private $siteNavigation = array();
    private $pagetitle = "Changeme";
    private $template = null;
    private $view = null;
    private $ajaxMacro = null;

    private $execjs = array();

    private $currentRound = null;

    private $achievements = array();

    private $playercount = null;

    public function __construct(
        SessionInterface $session,
        SessionStringRepositoryInterface $sessionStringRepository,
        $tpl_file,
        $pagetitle
    ) {
        $this->session = $session;
        $this->sessionStringRepository = $sessionStringRepository;
        $this->startBenchmark();
        $this->pagetitle = $pagetitle;
        $this->setTemplateFile($tpl_file);
    }

    protected function getTemplate()
    {
        if ($this->template === null) {
            $this->template = new TalPage($this->tpl_file);
        }
        return $this->template;
    }

    private $callbacks = [];

    protected function addCallBack($cb, $func, $session = false)
    {
        $this->callbacks[$cb] = [$func, $session];
    }

    private $viewOverride = false;

    private $views = [];

    protected function addView($view, $func, $override = false)
    {
        $this->views[$view] = [$func, $override];
    }

    protected function getView()
    {
        return $this->view;
    }

    private $viewContext = [];

    public function setView(string $view, array $viewContext = []): void
    {
        request::setVar($view, 1);

        $this->viewContext = $viewContext;
    }

    public function getViewContext(): array {
        return $this->viewContext;
    }

    private function maintenanceView()
    {
        if (!$this->isAdmin()) {
            if ($this->getGameState() == CONFIG_GAMESTATE_VALUE_TICK) {
                $this->setPageTitle("Rundenwechsel aktiv");
                $this->setTemplateFile('html/tick.xhtml');
            }
            if ($this->getGameState() == CONFIG_GAMESTATE_VALUE_MAINTENANCE) {
                $this->setPageTitle("Wartungsmodus");
                $this->setTemplateFile('html/maintenance.xhtml');
            }
        }
        return;
    }

    public function getGameState()
    {
        return $this->getGameConfigValue(CONFIG_GAMESTATE)->getValue();
    }

    public function setTemplateFile($tpl)
    {
        $this->tpl_file = $tpl;
        $this->getTemplate()->setTemplate($tpl);
    }

    public function setAjaxMacro($macro)
    {
        $this->ajaxMacro = $macro;
    }

    public function showAjaxMacro($macro)
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro($macro);
    }

    public function getAjaxMacro()
    {
        return $this->ajaxMacro;
    }

    private function startBenchmark()
    {
        $this->benchmarkTimer = microtime();
    }

    public function getMemoryUsage()
    {
        return round((memory_get_peak_usage() / 1024) / 1024, 3);
    }

    public function addInformationf(string $text, ...$args): void {
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

    public function addInformationMerge($info)
    {
        $this->gameInformations = array_merge($info, $this->getInformation());
    }

    protected function addInformationMergeDown(&$info)
    {
        $this->gameInformations = array_merge($this->getInformation(), $info);
    }

    function getInformation()
    {
        return $this->gameInformations;
    }

    public function sendInformation($recipient_id, $sender_id = USER_NOONE, $category_id = PM_SPECIAL_MAIN)
    {
        PM::sendPM($sender_id, $recipient_id, join('<br />', $this->getInformation()), $category_id);
    }

    public function hasInformation()
    {
        return count($this->getInformation()) > 0;
    }

    public function setTemplateVar(string $key, $variable)
    {
        $this->getTemplate()->setRef($key, $variable);
    }

    protected function render()
    {
        $user = $this->getUser();

        if (!$this->viewOverride && $this->getGameConfigValue(CONFIG_GAMESTATE)->getValue() != CONFIG_GAMESTATE_VALUE_ONLINE) {
            $this->maintenanceView();
        }
        $tpl = $this->getTemplate();
        $tpl->setRef("THIS", $this);
        $tpl->setVar("GFX", GFX_PATH);
        $tpl->setRef("USER", $user);

        if ($user !== null) {
            $this->setTemplateVar('PM_NAVLET', PMCategory::getNavletCategories($user->getId()));
        }

        $tpl->parse();
    }

    public function getUser(): ?UserData {
        return $this->session->getUser();
    }

    public function getBenchmark()
    {
        $start = explode(' ', $this->benchmarkTimer);
        $s_timer = $start[1] + $start[0];
        $end = explode(' ', microtime());
        $e_timer = $end[1] + $end[0];
        return round($e_timer - $s_timer, 6);;
    }

    public function getPlayerCount()
    {
        if ($this->playercount === null) {
            $this->playercount = DB()->query("SELECT COUNT(*) FROM stu_user WHERE id>100", 1);
        }
        return $this->playercount;
    }

    private $gameConfig = null;

    public function getGameConfig()
    {
        if ($this->gameConfig === null) {
            $this->gameConfig = GameConfig::getObjectsBy();
        }
        return $this->gameConfig;
    }

    public function getGameConfigValue($value)
    {
        return $this->getGameConfig()[$value];
    }

    public function getUniqHandle()
    {
        $this->handleId++;
        return "hdl" . $this->handleId;
    }

    function addNavigationPart(Tuple $part)
    {
        $this->siteNavigation[] = $part;
    }

    public function appendNavigationPart(
        string $url,
        string $title
    )
    {
        $this->addNavigationPart(new Tuple(
            $url,
            $title
        ));
    }

    public function getNavigation()
    {
        return $this->siteNavigation;
    }

    public function getPageTitle()
    {
        return $this->pagetitle;
    }

    public function setPageTitle($title)
    {
        $this->pagetitle = $title;
    }

    public function getQueryCount()
    {
        return DB()->getQueryCount();
    }

    public function getDebugNotices()
    {
        return get_debug_error()->getDebugNotices();
    }

    public function hasExecuteJS()
    {
        return count($this->execjs);
    }

    public function getExecuteJS()
    {
        return $this->execjs;
    }

    public function addExecuteJS($value)
    {
        $this->execjs[] = $value;
    }

    protected function showNoop()
    {
        exit;
    }

    public function getGameVersion()
    {
        return GAME_VERSION;
    }

    public function redirectTo(string $href): void
    {
        DB()->commitTransaction();
        header('Location: ' . $href);
        exit;
    }

    public function getCurrentRound()
    {
        if ($this->currentRound === null) {
            $this->currentRound = GameTurn::getCurrentTurn();
        }
        return $this->currentRound;
    }

    public function isDebugMode()
    {
        return isDebugMode();
    }

    public function getJavascriptPath()
    {
        return 'version_' . $this->getGameVersion();
    }

    public function getPlanetColonyLimit()
    {
        return DB()->query(
            'SELECT SUM(upper_planetlimit)+1 FROM stu_research WHERE id IN (SELECT research_id FROM stu_researched WHERE user_id=' . $this->getUser()->getId() . ' ANd aktiv=0)',
            1
        );
    }

    public function getMoonColonyLimit()
    {
        return DB()->query(
            'SELECT SUM(upper_moonlimit) FROM stu_research WHERE id IN (SELECT research_id FROM stu_researched WHERE user_id=' . $this->getUser()->getId() . ' ANd aktiv=0)',
            1
        );
    }

    public function getPlanetColonyCount()
    {
        return Colony::countInstances('WHERE user_id=' . $this->getUser()->getId() . ' AND colonies_classes_id IN (SELECT id FROM stu_colonies_classes WHERE is_moon=0)');
    }

    public function getMoonColonyCount()
    {
        return Colony::countInstances('WHERE user_id=' . $this->getUser()->getId() . ' AND colonies_classes_id IN (SELECT id FROM stu_colonies_classes WHERE is_moon=1)');
    }

    public function isAdmin()
    {
        return $this->getUser()->isAdmin();
    }

    public function checkDatabaseItem($databaseEntryId): void
    {
        // @todo refactor
        global $container;
        $databaseUserRepo = $container->get(DatabaseUserRepositoryInterface::class);

        $userId = $this->getUser()->getId();

        if ($databaseEntryId > 0 && $databaseUserRepo->exists($userId, $databaseEntryId) === false) {
            $this->addAchievement(databaseScan($databaseEntryId, $userId));
        }
    }

    protected function addAchievement($text)
    {
        $this->achievements[] = $text;
    }

    public function getAchievements()
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

    public function main(bool $session_check = true): void
    {
        $this->session->createSession($session_check);

        if ($session_check === false) {
            $this->session->checkLoginCookie();
        }

        $this->executeCallback();
        $this->executeView();

        $this->render();
    }

    private function sessionIsSafe(): bool {
        return $this->sessionStringRepository->isValid(
            (string) request::indString('sstr'),
            $this->getUser()->getId()
        );
    }

    private function executeCallback(): void
    {
        foreach ($this->callbacks as $key => $config) {
            if (request::indString($key)) {
                list($callable, $session_check) = $config;
                if (is_object($callable)) {
                    $callable->handle($this);
                    return;
                }
                if (!method_exists($this, $callable)) {
                    throw new InvalidCallbackException;
                }
                if ($session_check === true && !request::isPost()) {
                    if (!$this->sessionIsSafe()) {
                        return;
                    }
                }
                call_user_func_array([$this, $callable], []);
                return;
            }
        }
    }

    private function executeView()
    {
        foreach ($this->views as $key => $config) {
            list($callable, $override) = $config;

            $this->viewOverride = $override;
            if (request::indString($key)) {
                if (is_object($callable)) {
                    $callable->handle($this);
                    return;
                }
                if (!method_exists($this, $callable)) {
                    throw new \Exception('Invalid view');
                }
                call_user_func_array([$this, $callable], []);
                return;
            }
        }

        $view = $this->views[static::DEFAULT_VIEW] ?? null;

        if ($view !== null) {
            /**
             * @var ViewControllerInterface $callable
             */
            list($callable, $override) = $view;

            $this->viewOverride = $override;
            $callable->handle($this);
        }
    }

    public function isRegistrationPossible(): bool
    {
        return true;
    }

    private $gameStats;

    public function getGameStats(): array
    {
        if ($this->gameStats === null) {
            $this->gameStats = [
                'turn' => $this->getCurrentRound(),
                'player' => $this->getPlayerCount(),
                'playeronline' => DB()->query("SELECT COUNT(*) FROM stu_user WHERE id>100 AND lastaction>" . time() . "-300", 1),
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

    private $loginError = '';

    public function setLoginError(string $error): void {
        $this->loginError = $error;
    }

    public function getLoginError(): string {
        return $this->loginError;
    }
}
