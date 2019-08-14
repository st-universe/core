<?php

namespace Stu\Lib;

use AllianceJobs;
use Colony;
use Contactlist;
use Crew;
use DatabaseUser;
use Fleet;
use KnComment;
use KNPosting;
use Notes;
use PMCategory;
use ResearchUser;
use RPGPlot;
use Ship;
use ShipBuildplans;
use TradeLicences;
use TradeOffer;
use TradeShoutbox;
use TradeStorage;
use User;
use UserData;

class UserDeletion
{

    private $user;

    public function __construct(UserData $user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function handleAlliance()
    {
        $alliance = AllianceJobs::getByFounder($this->getUser()->getId());
        if ($alliance) {
            $alliance->getAlliance()->handleFounderDeletion();
        }
        AllianceJobs::delByUser('WHERE user_id=' . $this->getUser()->getId());
    }

    public function handleBuildplans()
    {
        $result = ShipBuildplans::getObjectsBy('user_id=' . $this->getUser()->getId());
        foreach ($result as $key => $obj) {
            $obj->deepDelete();
        }
    }

    public function handleColonies()
    {
        $result = Colony::getListBy('user_id=' . $this->getUser()->getId());
        foreach ($result as $key => $obj) {
            $obj->deepDelete();
        }
    }

    public function handleContactlist()
    {
        Contactlist::truncate('WHERE user_id=' . $this->getUser()->getId() . ' OR recipient=' . $this->getUser()->getId());
    }

    public function handleCrew()
    {
        foreach (Crew::getObjectsBy('WHERE user_id=' . $this->getUser()->getId()) as $key => $obj) {
            $obj->deepDelete();
        }
    }

    public function handleDatabaseEntries()
    {
        DatabaseUser::truncate('WHERE user_id=' . $this->getUser()->getId());
    }

    public function handleFleets()
    {
        Fleet::truncate('WHERE user_id=' . $this->getUser()->getId());
    }

    public function handleIgnoreList()
    {
        Contactlist::truncate('WHERE user_id=' . $this->getUser()->getId());
    }

    public function handleKnPostings()
    {
        foreach (KNPosting::getBy('WHERE user_id=' . $this->getUser()->getId()) as $key => $obj) {
            $obj->deleteAuthor();
        }
    }

    public function handleKnComments()
    {
        KnComment::truncate('WHERE user_id=' . $this->getUser()->getId());
    }

    public function handleNotes()
    {
        Notes::truncate('WHERE user_id=' . $this->getUser()->getId());
    }

    public function handleRPGPlots()
    {
        foreach (RPGPlot::getObjectsBy('WHERE user_id=' . $this->getUser()->getId()) as $key => $obj) {
            $obj->deleteOwner();
        }
    }

    public function handlePMCategories()
    {
        foreach (PMCategory::getObjectsBy('WHERE user_id=' . $this->getUser()->getId()) as $key => $obj) {
            $obj->deepDelete();
        }
    }

    public function handleResearch()
    {
        ResearchUser::truncate('WHERE user_id=' . $this->getUser()->getId());
    }

    public function handleShips()
    {
        foreach (Ship::getObjectsBy('WHERE user_id=' . $this->getUser()->getId()) as $key => $obj) {
            $obj->deepDelete();
        }
    }

    public function handleTrade()
    {
        TradeLicences::truncate('WHERE user_id=' . $this->getUser()->getId());
        TradeOffer::truncate('WHERE user_id=' . $this->getUser()->getId());
        TradeStorage::truncate('WHERE user_id=' . $this->getUser()->getId());
        TradeShoutbox::truncate('WHERE user_id=' . $this->getUser()->getId());
    }

    static function handle($userlist)
    {
        foreach ($userlist as $key => $user) {
            $handler = new UserDeletion($user);
            $handler->handleAlliance();
            $handler->handleBuildplans();
            $handler->handleColonies();
            $handler->handleContactlist();
            $handler->handleCrew();
            $handler->handleDatabaseEntries();
            $handler->handleFleets();
            $handler->handleIgnoreList();
            $handler->handleKnPostings();
            $handler->handleKnComments();
            $handler->handleNotes();
            $handler->handleRPGPlots();
            $handler->handlePMCategories();
            $handler->handleResearch();
            $handler->handleShips();
            $handler->handleTrade();
            $user->deepDelete();
        }
    }

    public static function handleIdleUsers()
    {
        self::handle(User::getUserListIdle());
    }

    public static function handleReset()
    {
        self::handle(User::getUserListReset());
    }

}
