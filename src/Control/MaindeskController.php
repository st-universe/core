<?php

namespace Stu\Control;

use AllianceTopic;
use Building;
use Colony;
use ColonyShipQueue;
use ContactlistData;
use InvalidParamException;
use KNPosting;
use request;
use Stu\Lib\SessionInterface;
use Tuple;
use User;
use UserProfileVisitors;

final class MaindeskController extends GameController
{

    private $default_tpl = "html/maindesk.xhtml";

    private $session;

    public function __construct(
        SessionInterface $session
    )
    {
        $this->session = $session;
        parent::__construct($session, $this->default_tpl, "/ Maindesk");
        $this->addNavigationPart(new Tuple("maindesk.php", "Maindesk"));

        $this->addCallBack("B_FIRST_COLONY", "getFirstColony");

        $this->addView("SHOW_COLONYLIST", "showColonyList");
        $this->addView("SHOW_BUDDYLIST", "showBuddylist");
        $this->addView('SHOW_COLONYLIST_AJAX', 'showColonyListAjax');
    }

    function displayFirstColonyDialogue()
    {
        if ($this->getUser()->getActive() == 1) {
            return true;
        }
        return false;
    }

    function getLogintimeFormatted()
    {
        return date("d.m.Y H:i", $this->session->getSessionVar('logintime'));
    }

    function getLastactionFormatted()
    {
        return date("d.m.Y H:i", $this->getUser()->getLastaction());
    }

    function showColonyList()
    {
        if (currentUser()->getActive() != 1) {
            new InvalidParamException('gfy');
        }
        $this->setTemplateFile("html/maindesk_colonylist.xhtml");
        $this->setPageTitle("Kolonie gründen");
        $this->addNavigationPart(new Tuple("?cb=getColonyList", "Kolonie gründen"));
    }

    function showBuddylist()
    {
        $this->setPageTitle("Freunde online");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/macros.xhtml/show_buddylist');
    }

    private $knpostings = null;

    /**
     */
    public function getNewKNPostings()
    {
        if ($this->knpostings === null) {
            $this->knpostings = KNPosting::getBy("WHERE id>" . currentUser()->getKnMark() . " LIMIT 3");
        }
        return $this->knpostings;
    }

    /**
     */
    public function getNewKNPostingCount()
    {
        return KNPosting::countInstances('id>' . currentUser()->getKnMark());
    }


    private $colonyList = null;

    public function getFreeColonyList()
    {
        if ($this->colonyList === null) {
            $this->colonyList = Colony::getFreeColonyList(currentUser()->getFaction());
        }
        return $this->colonyList;
    }

    function getFirstColony()
    {
        if (currentUser()->getActive() != 1) {
            new InvalidParamException('GFY');
        }
        DB()->beginTransaction();
        $colonyId = request::getIntFatal('id');
        $colony = new Colony($colonyId);
        if (!$colony->isFree()) {
            $this->addInformation("Dieser Planet wurde bereits besiedelt");
            return;
        }
        if (!array_key_exists($colonyId, Colony::getFreeColonyList(currentUser()->getFaction()))) {
            return;
        }

        $faction = ResourceCache()->getObject(CACHE_FACTION, currentUser()->getFaction());
        $colony->colonize(new Building($faction->getBuildingId()));

        currentUser()->setActive(2);
        currentUser()->save();

        // Database entries for planettype
        $this->checkDatabaseItem($colony->getPlanetType()->getDatabaseId());
        DB()->commitTransaction();
        $this->redirectTo('./colony.php?id=' . $colony->getId());
    }

    private $randomOnlineUser = null;

    public function getRandomOnlineUsers()
    {
        if ($this->randomOnlineUser === null) {
            $this->randomOnlineUser = User::getListBy('WHERE id!=' . currentUser()->getId() . ' AND (show_online_status=1 OR id IN (SELECT user_id FROM stu_contactlist WHERE mode=' . ContactlistData::CONTACT_FRIEND . ' AND recipient=' . currentUser()->getId() . ')) AND lastaction>' . (time() - USER_ONLINE_PERIOD) . ' ORDER BY RAND() LIMIT 15');
        }
        return $this->randomOnlineUser;
    }

    private $recentProfileVisitors = null;

    public function getRecentProfileVisitors()
    {
        if ($this->recentProfileVisitors === null) {
            $this->recentProfileVisitors = UserProfileVisitors::getRecentList(currentUser()->getId());
        }
        return $this->recentProfileVisitors;
    }

    private $allianceposts = null;

    function getLatestAllianceBoardTopics()
    {
        if ($this->allianceposts === null) {
            $this->allianceposts = AllianceTopic::getLatestTopics(currentUser()->getAllianceId());
        }
        return $this->allianceposts;
    }

    /**
     */
    protected function showColonyListAjax()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/sitemacros.xhtml/colonylist');
    }

    private $shipqueue = null;

    /**
     */
    public function getShipBuildProgress()
    {
        if ($this->shipqueue === null) {
            $this->shipqueue = ColonyShipQueue::getByUserId(currentUser()->getId());
        }
        return $this->shipqueue;
    }
}
