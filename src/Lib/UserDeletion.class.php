<?php

namespace Stu\Lib;

use AllianceJobs;
use Colony;
use Contactlist;
use Crew;
use Fleet;
use PMCategory;
use RPGPlot;
use Ship;
use ShipBuildplans;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\NoteRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use TradeLicences;
use TradeOffer;
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
        // @todo refactor
        global $container;

        $container->get(DatabaseUserRepositoryInterface::class)->truncateByUserId(
            (int) $this->getUser()->getId()
        );
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
        // @todo refactor
        global $container;

        $knPostRepo = $container->get(KnPostRepositoryInterface::class);

        foreach ($knPostRepo->getByUser((int) $this->getUser()->getId()) as $key => $obj) {
            $obj->setUserName($this->getUser()->getName());
            $obj->setUserId(0);

            $knPostRepo->save($obj);
        }
    }

    public function handleKnComments()
    {
        // @todo refactor
        global $container;

        $container->get(KnCommentRepositoryInterface::class)->truncateByUser((int) $this->getUser()->getId());
    }

    public function handleNotes()
    {
        // @todo refactor
        global $container;

        $container->get(NoteRepositoryInterface::class)->truncateByUserId((int) $this->getUser()->getId());
    }

    public function handleRPGPlots()
    {
        // @todo refactor
        global $container;

        $rpgPlotMemberRepo = $container->get(RpgPlotMemberRepositoryInterface::class);

        /** @var \RPGPlotData $obj
         */
        foreach (RPGPlot::getObjectsBy('WHERE user_id=' . $this->getUser()->getId()) as $key => $obj) {

            $item = $rpgPlotMemberRepo->getByPlotAndUser((int) $obj->getId(), (int) $this->getUser()->getId());
            if ($item !== null) {
                $rpgPlotMemberRepo->delete($item);
            }
            if ($obj->getMembers()) {
                $member = current($obj->getMembers());
                $obj->setUserId($member->getUserId());
                $obj->save();
                return;
            }
            $obj->setUserId(USER_NOONE);
            $obj->save();
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
        // @todo refactor
        global $container;

        $container->get(ResearchedRepositoryInterface::class)->truncateForUser((int) $this->getUser()->getId());
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

        // @todo refactor
        global $container;

        $container->get(TradeShoutboxRepositoryInterface::class)->truncateByUser((int) $this->getUser()->getId());
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

            DB()->query('DELETE FROM stu_user_map WHERE user_id='.$user->getId());
            DB()->query('DELETE FROM stu_user_iptable WHERE user_id='.$user->getId());

            // @todo refactor
            global $container;

            $container->get(SessionStringRepositoryInterface::class)->truncate((int) $user->getId());
            $container->get(UserProfileVisitorRepositoryInterface::class)->truncateByUser((int) $user->getId());

            $user->deleteFromDatabase();
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
