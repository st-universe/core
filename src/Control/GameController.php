<?php

namespace Stu\Control;

use Colony;
use DatabaseUser;
use GameConfig;
use GameTurn;
use HistoryEntry;
use InvalidCallbackException;
use PM;
use PMCategory;
use request;
use session;
use TalPage;
use Tuple;
use User;

abstract class GameController extends session
{

    private $tpl_file = null;
    private $gameInformations = array();
    private $benchmarkTimer = null;
    private $handleId = 0;
    private $siteNavigation = array();
    private $pagetitle = "Changeme";
    private $template = null;
    private $view = null;
    private $ajaxMacro = null;

    function __construct(&$tpl_file, $pagetitle)
    {
        $this->startBenchmark();
        parent::__construct();

        $this->addCallBack('B_VERIFY_LOGIN', 'verifyLoginCaptcha');

        $this->setPagetitle($pagetitle);
        $this->setTemplateFile($tpl_file);
    }

    function getTemplate()
    {
        if ($this->template === null) {
            $this->template = new TalPage($this->tpl_file);
        }
        return $this->template;
    }

    private $callback = null;

    function addCallBack($cb, $func, $session = false)
    {
        if ($this->callback !== null) {
            return;
        }
        if (request::indString($cb)) {
            if (!method_exists($this, $func)) {
                throw new InvalidCallbackException;
            }
            if ($session === true && !request::isPost()) {
                if (!$this->sessionIsSafe()) {
                    return;
                }
            }
            $this->callback = $cb;
            $this->$func();
            return;
        }
    }

    private $viewOverride = false;

    protected function addView($view, $func, $override = false)
    {
        if ($this->getView() !== null) {
            return;
        }
        $this->viewOverride = $override;
        if (request::indString($view)) {
            if (!method_exists($this, $func)) {
                throw new \Exception('Invalid view');
            }
            $this->$func();
            $this->view = $view;
            return;
        }
    }

    private function getViewOverride()
    {
        return $this->viewOverride;
    }

    function getView()
    {
        return $this->view;
    }

    function setView($view)
    {
        request::setVar($view, 1);
    }

    private function maintenanceView()
    {
        if (!isAdmin(currentUser()->getId())) {
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

    function setTemplateFile($tpl)
    {
        $this->tpl_file = &$tpl;
        $this->getTemplate()->setTemplate($tpl);
    }

    function setAjaxMacro($macro)
    {
        $this->ajaxMacro = $macro;
    }

    /**
     */
    protected function showAjaxMacro($macro)
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro($macro);
    }

    function getAjaxMacro()
    {
        return $this->ajaxMacro;
    }

    function startBenchmark()
    {
        $this->benchmarkTimer = microtime();
    }

    function getMemoryUsage()
    {
        return round((memory_get_usage() / 1024) / 1024, 3);
    }

    protected function addInformation($msg, $override = false)
    {
        if ($override) {
            $this->gameInformations = array();
        }
        $this->gameInformations[] = decodeString($msg);
    }

    protected function addInformationMerge(&$info)
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

    /**
     */
    protected function sendInformation($recipient_id, $sender_id = USER_NOONE, $category_id = PM_SPECIAL_MAIN)
    {
        PM::sendPM($sender_id, $recipient_id, join('<br />', $this->getInformation()), $category_id);
    }

    function hasInformation()
    {
        return count($this->getInformation()) > 0;
    }

    function render(&$page)
    {
        if (!$this->getViewOverride() && $this->getGameConfigValue(CONFIG_GAMESTATE)->getValue() != CONFIG_GAMESTATE_VALUE_ONLINE) {
            $this->maintenanceView();
        }
        $tpl =& $this->getTemplate();
        $tpl->setRef("THIS", $page);
        $tpl->setVar("GFX", GFX_PATH);
        $tpl->setRef("USER", currentUser());
        $tpl->parse();
    }

    function renderIndexSite(&$page)
    {
        $tpl = &$this->getTemplate();
        $tpl->setVar("THIS", $page);
        $tpl->parse();
    }

    function getBenchmark()
    {
        $start = explode(' ', $this->benchmarkTimer);
        $s_timer = $start[1] + $start[0];
        $end = explode(' ', microtime());
        $e_timer = $end[1] + $end[0];
        return round($e_timer - $s_timer, 6);;
    }

    private $playercount = null;

    function getPlayerCount()
    {
        if ($this->playercount === null) {
            $this->playercount = DB()->query("SELECT COUNT(*) FROM stu_user WHERE id>100", 1);
        }
        return $this->playercount;
    }

    private $onlinePlayercount = null;

    function getOnlinePlayerCount()
    {
        if ($this->onlinePlayercount === null) {
            $this->onlinePlayercount = DB()->query("SELECT COUNT(*) FROM stu_user WHERE id>100 AND lastaction>" . time() . "-300",
                1);
        }
        return $this->onlinePlayercount;
    }

    function parseBBC(&$var)
    {
        return BBCode()->parse($var);
    }

    function getUserName()
    {
        return BBCode()->parse($this->getUser()->user);
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

    function getUniqHandle()
    {
        $this->handleId++;
        return "hdl" . $this->handleId;
    }

    function addNavigationPart(Tuple $part)
    {
        $this->siteNavigation[] = $part;
    }

    function getNavigation()
    {
        return $this->siteNavigation;
    }

    function getPageTitle()
    {
        return $this->pagetitle;
    }

    function setPageTitle($title)
    {
        $this->pagetitle = $title;
    }

    function getQueryCount()
    {
        return DB()->getQueryCount();
    }

    function getDebugNotices()
    {
        return get_debug_error()->getDebugNotices();
    }

    private $execjs = array();

    function hasExecuteJS()
    {
        return count($this->execjs);
    }

    function getExecuteJS()
    {
        return $this->execjs;
    }

    function addExecuteJS($value)
    {
        $this->execjs[] = $value;
    }

    function showNoop()
    {
        exit;
    }

    function getGameVersion()
    {
        return GAME_VERSION;
    }

    function redirectTo($href)
    {
        header('Location: ' . $href);
        exit;
    }

    function getFriendsOnline()
    {
        return User::getListBy("WHERE lastaction>" . (time() - USER_ONLINE_PERIOD) . " AND (id IN (SELECT user_id FROM stu_contactlist WHERE mode=1 AND recipient=" . currentUser()->getId() . ")
				      OR id IN (SELECT id FROM stu_user WHERE allys_id>0 AND allys_id=" . currentUser()->getAllianceId() . ")) AND id!=" . currentUser()->getId() . " GROUP BY id ORDER BY RAND() LIMIT 10");
    }

    function getFriendsOnlineCount()
    {
        return User::countInstances("WHERE lastaction>" . (time() - USER_ONLINE_PERIOD) . " AND (id IN (SELECT user_id FROM stu_contactlist WHERE mode=1 AND recipient=" . currentUser()->getId() . ")
				      OR id IN (SELECT id FROM stu_user WHERE allys_id>0 AND allys_id=" . currentUser()->getAllianceId() . ")) AND id!=" . currentUser()->getId());
    }

    private $currentRound = null;

    function getCurrentRound()
    {
        if ($this->currentRound === null) {
            $this->currentRound = GameTurn::getCurrentTurn();
        }
        return $this->currentRound;
    }

    private $pmnavlet = null;

    public function getNewPMNavlet()
    {
        if ($this->pmnavlet === null) {
            $this->pmnavlet = PMCategory::getNavletCategories();
        }
        return $this->pmnavlet;
    }

    /**
     */
    public function isDebugMode()
    {
        return isDebugMode();
    }

    /**
     */
    public function getJavascriptPath()
    {
        return 'version_' . $this->getGameVersion();
    }

    /**
     */
    public function getPlanetColonyLimit()
    {
        return DB()->query('SELECT SUM(upper_planetlimit)+1 FROM stu_research WHERE id IN (SELECT research_id FROM stu_researched WHERE user_id=' . currentUser()->getId() . ' ANd aktiv=0)',
            1);
    }

    /**
     */
    public function getMoonColonyLimit()
    {
        return DB()->query('SELECT SUM(upper_moonlimit) FROM stu_research WHERE id IN (SELECT research_id FROM stu_researched WHERE user_id=' . currentUser()->getId() . ' ANd aktiv=0)',
            1);
    }

    /**
     */
    public function getPlanetColonyCount()
    {
        return Colony::countInstances('WHERE user_id=' . currentUser()->getId() . ' AND colonies_classes_id IN (SELECT id FROM stu_colonies_classes WHERE is_moon=0)');
    }

    /**
     */
    public function getMoonColonyCount()
    {
        return Colony::countInstances('WHERE user_id=' . currentUser()->getId() . ' AND colonies_classes_id IN (SELECT id FROM stu_colonies_classes WHERE is_moon=1)');
    }

    /**
     */
    public function isAdmin()
    {
        return currentUser()->isAdmin();
    }

    /**
     */
    function getRecentHistory()
    {
        if ($this->recent_history === null) {
            $this->recent_history = HistoryEntry::getListBy('ORDER BY id DESC LIMIT 10');
        }
        return $this->recent_history;
    }

    /**
     */
    protected function checkDatabaseItem($database_entry_id)
    {
        if ($database_entry_id > 0 && !DatabaseUser::checkEntry($database_entry_id, currentUser()->getId())) {
            $this->addAchievement(databaseScan($database_entry_id, currentUser()->getId()));
        }
    }

    private $achievements = array();

    /**
     */
    protected function addAchievement($text)
    {
        $this->achievements[] = $text;
    }

    /**
     */
    function getAchievements()
    {
        return $this->achievements;
    }
}
