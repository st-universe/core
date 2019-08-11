<?php

namespace Stu\Control;

use AccessViolation;
use DatabaseCategory;
use DatabaseEntry;
use DatabaseTopListDiscover;
use MapRegion;
use request;
use ShipRump;
use StarSystem;
use Stu\Lib\Session;
use Tuple;
use User;

final class DatabaseController extends GameController
{

    private $default_tpl = "html/database.xhtml";

    function __construct(
        Session $session
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Datenbank");
        $this->addNavigationPart(new Tuple("database.php", "Datenbank"));

        $this->addCallBack('B_CANCEL_CONTRACT', 'cancelContract', true);

        $this->addView("SHOW_CATEGORY", "showCategory");
        $this->addView("SHOW_ENTRY", "showEntry");
        $this->addView("SHOW_SETTLERLIST", "showUserList");
        $this->addView("SHOW_TOP_DISCOVER", "showTopResearchUser");
    }

    private $rumplist = null;

    function getShipRumplist()
    {
        if ($this->rumplist === null) {
            $this->rumplist = DatabaseCategory::getCategoriesByType(DATABASE_TYPE_SHIPRUMP);
        }
        return $this->rumplist;
    }

    function getRPGShiplist()
    {
        return DatabaseCategory::getCategoriesByType(DATABASE_TYPE_RPGSHIPS);
    }

    public function getPlacesList()
    {
        return DatabaseCategory::getCategoriesByType(DATABASE_TYPE_TRADEPOSTS);
    }

    public function getMapList()
    {
        return DatabaseCategory::getCategoriesByType(DATABASE_TYPE_MAP);
    }

    function showCategory()
    {
        $this->addNavigationPart(new Tuple("database.php?SHOW_CATEGORY=1&cat=" . $this->getCategory()->getId(),
            "Datenbank: " . $this->getCategory()->getDescription()));
        $this->setPageTitle("/ Datenbank: " . $this->getCategory()->getDescription());
        $this->setTemplateFile('html/databasecategory.xhtml');
    }

    function showEntry()
    {
        if (!currentUser()->checkDatabaseEntry($this->getDatabaseEntry()->getId())) {
            throw new AccessViolation();
        }
        $this->addNavigationPart(new Tuple("database.php?SHOW_CATEGORY=1&cat=" . $this->getCategory()->getId(),
            "Datenbank: " . $this->getCategory()->getDescription()));
        $this->addNavigationPart(new Tuple("database.php?SHOW_ENTRY=1&cat=" . $this->getCategory()->getId() . "&ent=" . $this->getDatabaseEntry()->getId(),
            "Eintrag: " . $this->getDatabaseEntry()->getDescription()));
        $this->addSpecialVars();
        $this->setPageTitle("/ Datenbankeintrag: " . $this->getDatabaseEntry()->getDescription());
        $this->setTemplateFile('html/databaseentry.xhtml');
    }

    protected function addSpecialVars()
    {
        switch ($this->getDatabaseEntry()->getType()) {
            case DATABASE_TYPE_REGION:
                $this->getTemplate()->setVar('REGION', new MapRegion($this->getDatabaseEntry()->getObjectId()));
                break;
            case DATABASE_TYPE_RUMP:
                $this->getTemplate()->setVar('RUMP', new ShipRump($this->getDatabaseEntry()->getObjectId()));
                break;
            case DATABASE_TYPE_STARSYSTEM:
                $this->getTemplate()->setVar('SYSTEM', new StarSystem($this->getDatabaseEntry()->getObjectId()));
                break;
        }
    }

    function showUserList()
    {
        $this->addNavigationPart(new Tuple("database.php?SHOW_SETTLERLIST=1", "Siedlerliste"));
        $this->setPageTitle("/ Siedlerliste");
        $this->setTemplateFile('html/userlist.xhtml');
    }

    /**
     */
    protected function showTopResearchUser()
    {
        $this->addNavigationPart(new Tuple("database.php?SHOW_TOP_DISCOVER=1", _('Die 10 besten Entdecker')));
        $this->setPageTitle(_('/ Datenbank / Die 10 besten Entdecker'));
        $this->showAjaxMacro('html/database.xhtml/top_research_user');
    }

    private $top_research_user = null;

    /**
     */
    public function getTopResearchUser()
    {
        if ($this->top_research_user === null) {
            $this->top_research_user = array();
            $result = DB()->query('SELECT a.user_id,SUM(c.points) as points FROM stu_database_user as a LEFT JOIN stu_database_entrys as b ON b.id=a.database_id LEFT JOIN stu_database_categories as c ON c.id=b.category_id GROUP BY a.user_id ORDER BY points DESC LIMIT 10');
            while ($entry = mysqli_fetch_assoc($result)) {
                $this->top_research_user[] = new DatabaseTopListDiscover($entry);
            }
        }
        return $this->top_research_user;
    }

    function getCategoryId()
    {
        return request::getIntFatal('cat');
    }

    private $entry = null;

    function getDatabaseEntry()
    {
        if ($this->entry === null) {
            $this->entry = new DatabaseEntry(request::getIntFatal('ent'));
        }
        return $this->entry;
    }

    private $category = null;

    function getCategory()
    {
        if ($this->category === null) {
            $this->category = new DatabaseCategory($this->getCategoryId());
        }
        return $this->category;
    }

    private $userlist = null;

    function getUserList()
    {
        if ($this->userlist === null) {
            switch ($this->getUserListOrder()) {
                case 'id':
                    $sort = 'id';
                    break;
                case "fac":
                    $sort = 'race';
                    break;
                case "alliance":
                    $sort = 'allys_id';
                    break;
                default:
                    $sort = 'id';
            }
            switch ($this->getUserListWay()) {
                case 'up':
                    $soway = 'DESC';
                    break;
                case 'down':
                    $soway = 'ASC';
                    break;
                default:
                    $soway = 'ASC';
            }
            $this->userlist = User::getListBy("WHERE id>100 ORDER BY " . $sort . " " . $soway . " LIMIT " . $this->getUserListMark() . "," . USERLISTLIMITER);
        }
        return $this->userlist;
    }

    function getUserListOrder()
    {
        return request::getString('order');
    }

    function getUserListWay()
    {
        return request::getString('way');
    }

    function getUserListMark()
    {
        return request::getInt('mark', 0);
    }

    private $ulnav = null;

    function getUserListNavigation()
    {
        if ($this->ulnav === null) {
            $mark = request::getInt('mark');
            if ($mark % USERLISTLIMITER != 0 || $mark < 0) {
                $mark = 0;
            }
            $maxcount = $this->getPlayerCount();
            $maxpage = ceil($maxcount / USERLISTLIMITER);
            $curpage = floor($mark / USERLISTLIMITER);
            $ret = array();
            if ($curpage != 0) {
                $ret[] = array("page" => "<<", "mark" => 0, "cssclass" => "pages");
                $ret[] = array("page" => "<", "mark" => ($mark - USERLISTLIMITER), "cssclass" => "pages");
            }
            for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
                if ($i > $maxpage || $i < 1) {
                    continue;
                }
                $ret[] = array(
                    "page" => $i,
                    "mark" => ($i * USERLISTLIMITER - USERLISTLIMITER),
                    "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
                );
            }
            if ($curpage + 1 != $maxpage) {
                $ret[] = array("page" => ">", "mark" => ($mark + USERLISTLIMITER), "cssclass" => "pages");
                $ret[] = array(
                    "page" => ">>",
                    "mark" => $maxpage * USERLISTLIMITER - USERLISTLIMITER,
                    "cssclass" => "pages"
                );
            }
            $this->ulnav = $ret;
        }
        return $this->ulnav;
    }
}
