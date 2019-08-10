<?php

namespace Stu\Control;

use AccessViolation;
use AllianceBoard;
use AllianceBoardData;
use AllianceData;
use AllianceJobs;
use AllianceJobsData;
use AlliancePost;
use AlliancePostData;
use AllianceRelation;
use AllianceRelationData;
use AllianceTopic;
use AllianceTopicData;
use Exception;
use HistoryEntry;
use PM;
use request;
use Tuple;
use User;

final class AllianceController extends GameController
{

    private $default_tpl = "html/alliancelist.xhtml";

    function __construct()
    {
        parent::__construct($this->default_tpl, "/ Allianzschirm");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_LIST=1", "Allianzliste"));
        if ($this->getAllianceId()) {
            $this->addNavigationPart(new Tuple("alliance.php?ALLIANCE_DETAILS=1&id=" . $this->getAlliance()->getId(),
                "Allianzschirm"));
        }

        $this->addCallback('B_CREATE_ALLIANCE', 'createAlliance', true);
        $this->addCallback('B_UPDATE_ALLIANCE', 'updateAlliance', true);
        $this->addCallback('B_SIGNUP_ALLIANCE', 'signupAlliance', true);
        $this->addCallback('B_ACCEPT_APPLICATION', 'acceptAllianceApplication', true);
        $this->addCallback('B_DECLINE_APPLICATION', 'declineAllianceApplication', true);
        $this->addCallback('B_KICK_USER', 'kickUser', true);
        $this->addCallback('B_DELETE_ALLIANCE', 'deleteAlliance', true);
        $this->addCallback('B_LEAVE_ALLIANCE', 'leaveAlliance', true);
        $this->addCallback('B_PROMOTE_USER', 'promoteUser', true);
        $this->addCallback('B_CHANGE_AVATAR', 'changeAvatar');
        $this->addCallback('B_DELETE_AVATAR', 'deleteAvatar');
        $this->addCallback('B_ADD_BOARD', 'addBoard');
        $this->addCallback('B_CREATE_TOPIC', 'createAllianceTopic', true);
        $this->addCallback('B_CREATE_POSTING', 'createAlliancePost', true);
        $this->addCallback('B_RENAME_TOPIC', 'renameTopic');
        $this->addCallback('B_DELETE_TOPIC', 'deleteTopic', true);
        $this->addCallback('B_RENAME_BOARD', 'renameBoard');
        $this->addCallback('B_DELETE_BOARD', 'deleteBoard', true);
        $this->addCallback('B_NEW_RELATION', 'newRelation');
        $this->addCallback('B_CANCEL_OFFER', 'cancelOffer', true);
        $this->addCallback('B_ACCEPT_OFFER', 'acceptOffer', true);
        $this->addCallback('B_SUGGEST_PEACE', 'suggestPeace', true);
        $this->addCallBack('B_CANCEL_CONTRACT', 'cancelContract', true);
        $this->addCallBack('B_DEL_POSTING', 'deletePosting', true);
        $this->addCallBack('B_SET_STICKY', 'setSticky');
        $this->addCallBack('B_UNSET_STICKY', 'unsetSticky');

        $this->addView('ALLIANCE_DETAILS', 'showAllianceDetails');
        $this->addView('CREATE_ALLIANCE', 'showCreateAlliance');
        $this->addView('SHOW_LIST', 'showAllianceList');
        $this->addView('SHOW_ALLIANCE', 'showAllianceDetails');
        $this->addView('EDIT_ALLIANCE', 'showAllianceEdit');
        $this->addView('SHOW_APPLICATIONS', 'showAllianceApplications');
        $this->addView('SHOW_MANAGEMENT', 'showAllianceManagement');
        $this->addView('SHOW_RELATIONS', 'showRelations');

        $this->addView('SHOW_BOARDS', 'showAllianceBoards');
        $this->addView('SHOW_BOARD', 'showAllianceBoard');
        $this->addView('SHOW_TOPIC', 'showAllianceTopic');
        $this->addView('SHOW_NEW_TOPIC', 'showAllianceNewTopic');
        $this->addView('SHOW_NEW_POST', 'showAllianceNewPost');
        $this->addView("SHOW_TOPIC_SETTINGS", "showTopicSettings");
        $this->addView("SHOW_BOARD_SETTINGS", "showBoardSettings");

        if (currentUser()->getAllianceId() > 0 && !$this->getView()) {
            $this->showAllianceDetails();
        }

        $this->render($this);
    }

    private $alliancelist = null;

    function getAllianceList()
    {
        if ($this->alliancelist === null) {
            $this->alliancelist = \Alliance::getList();
        }
        return $this->alliancelist;
    }

    function showAllianceList()
    {
        $this->setTemplateFile('html/alliancelist.xhtml');
    }

    function showAllianceDetails()
    {
        $this->setPageTitle("Allianz anzeigen");
        $this->setTemplateFile('html/alliancedetails.xhtml');
    }

    function showAllianceEdit()
    {
        if (!$this->getAlliance()->isNew()) {
            if (!$this->getAlliance()->currentUserMayEdit()) {
                new AccessViolation;
            }
            $this->setPageTitle("Allianz editieren");
            $this->addNavigationPart(new Tuple("alliance.php?ALLIANCE_DETAILS=1&id=" . $this->getAlliance()->getId(),
                "Allianz anzeigen"));
            $this->addNavigationPart(new Tuple("alliance.php?EDIT_ALLIANCE=1&id=" . $this->getAlliance()->getId(),
                "Allianz editieren"));
        }
        $this->setTemplateFile('html/allianceedit.xhtml');
    }

    function showAllianceApplications()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $this->setPageTitle("Allianz anzeigen");
        $this->addNavigationPart(new Tuple("alliance.php?ALLIANCE_DETAILS=1&id=" . $this->getAlliance()->getId(),
            "Allianz anzeigen"));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_APPLICATIONS=1&id=" . $this->getAlliance()->getId(),
            "Bewerbungen"));
        $this->setTemplateFile('html/allianceapplications.xhtml');
    }

    function showTopicSettings()
    {
        $this->setPageTitle("Thema editieren");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/alliancemacros.xhtml/topic_settings');
    }

    function showBoardSettings()
    {
        $this->setPageTitle("Forum editieren");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/alliancemacros.xhtml/board_settings');
    }

    function showAllianceManagement()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $this->setPageTitle("Allianz anzeigen");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_MANAGEMENT=1&id=" . $this->getAlliance()->getId(),
            "Verwaltung"));
        $this->setTemplateFile('html/alliancemanagement.xhtml');
    }

    function showRelations()
    {
        if (!$this->getAlliance()->currentUserIsDiplomatic()) {
            new AccessViolation;
        }
        $this->setPageTitle("Diplomatie");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_RELATIONS=1&id=" . $this->getAlliance()->getId(),
            "Diplomatie"));
        $this->setTemplateFile('html/alliancerelations.xhtml');
    }

    function showAllianceBoards()
    {
        $this->enforceAllianceCheck();
        $this->setPageTitle("Allianzforum");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARDS=1&id=" . $this->getAlliance()->getId(),
            "Allianzforum"));
        $this->setTemplateFile('html/allianceboard.xhtml');
    }

    function showAllianceBoard()
    {
        $this->enforceAllianceCheck();
        $this->setPageTitle("Allianzforum");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARDS=1&id=" . $this->getAlliance()->getId(),
            "Allianzforum"));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARD=1&bid=" . $this->getBoard()->getId() . "&id=" . $this->getAlliance()->getId(),
            $this->getBoard()->getName()));
        $this->setTemplateFile('html/allianceboardtopics.xhtml');
    }

    function showAllianceTopic()
    {
        $this->enforceAllianceCheck();
        $this->setPageTitle("Allianzforum");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARDS=1&id=" . $this->getAlliance()->getId(),
            "Allianzforum"));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARD=1&bid=" . $this->getBoard()->getId() . "&id=" . $this->getAlliance()->getId(),
            $this->getBoard()->getName()));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_TOPIC=1&bid=" . $this->getBoard()->getId() . "&tid=" . $this->getTopic()->getId() . "&id=" . $this->getAlliance()->getId(),
            $this->getTopic()->getName()));
        $this->setTemplateFile('html/allianceboardtopic.xhtml');
    }

    function showAllianceNewTopic()
    {
        $this->setPageTitle("Allianzforum");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARDS=1&id=" . $this->getAlliance()->getId(),
            "Allianzforum"));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARD=1&bid=" . $this->getBoard()->getId() . "&id=" . $this->getAlliance()->getId(),
            $this->getBoard()->getName()));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_NEW_TOPIC=1&bid=" . $this->getBoard()->getId() . "&id=" . $this->getAlliance()->getId(),
            "Thema erstellen"));
        $this->setTemplateFile('html/allianceboardcreatetopic.xhtml');
    }

    function showAllianceNewPost()
    {
        $this->setPageTitle("Allianzforum");
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARDS=1&id=" . $this->getAlliance()->getId(),
            "Allianzforum"));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_BOARD=1&bid=" . $this->getBoard()->getId() . "&id=" . $this->getAlliance()->getId(),
            $this->getBoard()->getName()));
        $this->addNavigationPart(new Tuple("alliance.php?SHOW_NEW_POST=1&bid=" . $this->getBoard()->getId() . "&id=" . $this->getAlliance()->getId(),
            "Antwort erstellen"));
        $this->setTemplateFile('html/allianceboardcreatetopic.xhtml');
    }

    function showCreateAlliance()
    {
        if (currentUser()->isInAlliance()) {
            new Exception();
        }
        if ($this->alliance === null) {
            $this->alliance = new AllianceData;
            $this->alliance->setDate(time());
        }
        $this->setPageTitle("Allianz erstellen");
        $this->addNavigationPart(new Tuple("alliance.php?CREATE_ALLIANCE=1", "Allianz erstellen"));
        $this->setTemplateFile('html/allianceedit.xhtml');
    }

    function getAllianceId()
    {
        if (request::indInt('id')) {
            return request::indInt('id');
        }
        if (currentUser()->isInAlliance()) {
            return currentUser()->getAllianceId();
        }
        return 0;
    }

    private $alliance = null;

    function getAlliance()
    {
        if ($this->alliance === null) {
            $this->alliance = new \Alliance($this->getAllianceId());
        }
        return $this->alliance;
    }

    private $board = null;

    function getBoard()
    {
        if ($this->board === null) {
            $bid = request::indInt('bid');
            $this->board = AllianceBoard::getByAlliance($bid, $this->getAlliance()->getId());
        }
        return $this->board;
    }

    private $topics = null;

    function getTopics()
    {
        if ($this->topics === null) {
            $bid = request::indInt('bid');
            $this->topics = AllianceTopic::getList('alliance_id=' . $this->getAlliance()->getId() . ' AND board_id=' . $bid . " ORDER BY sticky DESC,last_post_date DESC");
        }
        return $this->topics;
    }

    private $topic = null;

    function getTopic()
    {
        if ($this->topic === null) {
            $tid = request::indInt('tid');
            $topic = AllianceTopic::getByAlliance($tid, $this->getAlliance()->getId());
            if (!$topic) {
                $topic = new AllianceTopicData;
            }
            $this->topic = $topic;
        }
        return $this->topic;
    }

    private $posting = null;

    function getPosting()
    {
        if ($this->posting === null) {
            $pid = request::indInt('pid');
            $post = AlliancePost::getByAlliance($pid, $this->getAlliance()->getId());
            if (!$post) {
                $post = new AlliancePostData;
            }
            $this->posting = $post;

        }
        return $this->posting;
    }

    function createAlliance()
    {
        $this->alliance = new AllianceData;
        $this->getAlliance()->setName(tidyString(request::postString('name')));
        $this->getAlliance()->setHomepage(tidyString(request::postString('homepage')));
        $this->getAlliance()->setDescription(tidyString(request::postString('description')));
        if (request::postString('factionid')) {
            $this->getAlliance()->setFactionId(currentUser()->getFaction());
        }
        if (strlen(strip_tags(trim($this->getAlliance()->getNameWithoutMarkup()))) < 5) {
            $this->setView('CREATE_ALLIANCE');
            $this->addInformation("Der Name muss aus mindestens 5 Zeichen bestehen");
            return;
        }
        if (strlen($this->getAlliance()->getHomepage()) > 0) {
            if (strpos($this->getAlliance()->getHomepage(), 'http') !== 0) {
                $this->setView('CREATE_ALLIANCE');
                $this->addInformation("Diese Homepage-Adresse ist nicht gültig");
                return;
            }
        }
        $this->getAlliance()->setDate(time());
        $this->getAlliance()->save();
        currentUser()->setAllianceId($this->getAlliance()->getId());
        currentUser()->save();
        AllianceJobs::delByUser(currentUser()->getId());

        $job = new AllianceJobsData;
        $job->setType(ALLIANCE_JOBS_FOUNDER);
        $job->setAllianceId($this->getAlliance()->getId());
        $job->setUserId(currentUser()->getId());
        $job->save();

        $this->addInformation("Die Allianz wurde gegründet");
        $this->setView('SHOW_ALLIANCE');
    }

    function updateAlliance()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $this->setView('EDIT_ALLIANCE');
        $this->getAlliance()->setName(tidyString(request::postString('name')));
        $this->getAlliance()->setHomepage(tidyString(request::postString('homepage')));
        $this->getAlliance()->setDescription(tidyString(request::postString('description')));
        if ($this->getAlliance()->mayEditFactionMode()) {
            if (request::postString('factionid')) {
                $this->getAlliance()->setFactionId(currentUser()->getFaction());
            } else {
                $this->getAlliance()->setFactionId(0);
            }
        }
        if (request::postInt('acceptapp') == 1) {
            $this->getAlliance()->setAcceptApplications(1);
        } else {
            $this->getAlliance()->setAcceptApplications(0);
            $this->truncateApplications();
        }

        if (strlen(strip_tags(trim($this->getAlliance()->getNameWithoutMarkup()))) < 5) {
            $this->addInformation("Der Name muss aus mindestens 5 Zeichen bestehen");
            return;
        }
        if (strlen($this->getAlliance()->getHomepage()) > 0) {
            if (strpos($this->getAlliance()->getHomepage(), 'http') !== 0) {
                $this->addInformation("Diese Homepage-Adresse ist nicht gültig");
                return;
            }
        }

        $this->getAlliance()->save();
        $this->addInformation("Die Allianz wurde editiert");
    }

    function truncateApplications()
    {
        AllianceJobs::truncatePendingMembers($this->getAlliance()->getId());
    }

    function signupAlliance()
    {
        if (!$this->getAlliance()->currentUserMaySignup()) {
            new AccessViolation;
        }
        $obj = new AllianceJobsData;
        $obj->setUserId(currentUser()->getId());
        $obj->setType(ALLIANCE_JOBS_PENDING);
        $obj->setAllianceId($this->getAlliance()->getId());
        $obj->save();

        $text = "Der Siedler " . currentUser()->getNameWithoutMarkup() . " hat sich für die Allianz beworben";
        PM::sendPM(currentUser()->getId(), $this->getAlliance()->getFounder()->getUserId(), $text);
        if ($this->getAlliance()->getSuccessor()) {
            PM::sendPM(currentUser()->getId(), $this->getAlliance()->getSuccessor()->getUserId(), $text);
        }

        $this->addInformation("Du hast Dich für die Allianz beworben");
    }

    function acceptAllianceApplication()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $appl = new AllianceJobs(request::getIntFatal('aid'));
        if ($appl->getAllianceId() != $this->getAlliance()->getId()) {
            new AccessViolation;
        }
        $appl->getUser()->setAllianceId($appl->getAllianceId());
        $appl->getUser()->save();
        $appl->deleteFromDatabase();

        $text = "Deine Bewerbung wurde akzeptiert - Du bist jetzt Mitglied der Allianz " . $this->getAlliance()->getNameWithoutMarkup();
        PM::sendPM(currentUser()->getId(), $appl->getUserId(), $text);

        $this->addInformation("Die Bewerbung wurde angenommen");
    }

    function declineAllianceApplication()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $appl = new AllianceJobs(request::getIntFatal('aid'));
        if ($appl->getAllianceId() != $this->getAlliance()->getId()) {
            new AccessViolation;
        }
        $appl->deleteFromDatabase();

        $text = "Deine Bewerbung bei der Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " wurde abgelehnt";
        PM::sendPM(USER_NOONE, $appl->getUserId(), $text);

        $this->addInformation("Die Bewerbung wurde abgelehnt");
    }

    function kickUser()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $uid = request::getIntFatal('uid');
        if ($uid == currentUser()->getId()) {
            $this->addInformation("Du kannst Dich nicht selbst rauswerfen");
            return;
        }
        $user = new User($uid);
        if ($user->getAllianceId() != $this->getAlliance()->getId()) {
            new AccessViolation;
        }
        $user->setAllianceId(0);
        $user->save();
        if ($this->getAlliance()->getFounder()->getUserId() == $uid) {
            $this->getAlliance()->setFounder(currentUser()->getId());
            $this->getAlliance()->delSuccessor();
        }
        AllianceJobs::delByUser($uid);

        $text = "Deine Mitgliedschaft in der Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " wurde beendet";
        PM::sendPM(USER_NOONE, $uid, $text);

        $this->addInformation("Der Siedler wurde rausgeworfen");
    }

    function deleteAlliance()
    {
        if (!$this->getAlliance()->currentUserIsFounder()) {
            new AccessViolation;
        }
        $this->getAlliance()->delete();
        currentUser()->setAllianceId(0);
        $this->setView('SHOW_LIST');
        $this->addInformation("Die Allianz wurde gelöscht");
    }

    function enforceAllianceCheck()
    {
        if ($this->getAlliance()->getId() != currentUser()->getAllianceId()) {
            new AccessViolation;
        }
    }

    function leaveAlliance()
    {
        if ($this->getAlliance()->currentUserIsFounder()) {
            new AccessViolation;
        }

        AllianceJobs::delByUser(currentUser()->getId());
        currentUser()->setAllianceId(0);
        currentUser()->save();
        $this->setSessionVar('allys_id', 0);

        $text = "Der Siedler " . currentUser()->getNameWithoutMarkup() . " hat die Allianz verlassen";
        PM::sendPM(currentUser()->getId(), $this->getAlliance()->getFounder()->getUserId(), $text);
        if ($this->getAlliance()->getSuccessor()) {
            PM::sendPM(currentUser()->getId(), $this->getAlliance()->getSuccessor()->getUserId(), $text);
        }

        $this->setView('SHOW_LIST');
        $this->addInformation("Du hast die Allianz verlassen");
    }

    function promoteUser()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $uid = request::getIntFatal('uid');
        $user = new User($uid);
        if ($user->getAllianceId() != $this->getAlliance()->getId()) {
            new AccessViolation;
        }
        $type = request::getIntFatal('type');
        if (!array_key_exists($type, AllianceJobs::getPossibleTypes())) {
            new AccessViolation;
        }
        if ($this->getAlliance()->getFounder()->getUserId() == $uid) {
            new AccessViolation;
        }
        AllianceJobs::delByUser($user->getId());
        switch ($type) {
            case ALLIANCE_JOBS_FOUNDER:
                if (!$this->getAlliance()->currentUserIsFounder()) {
                    new AccessViolation;
                }
                $this->getAlliance()->setFounder($user->getId());
                $text = "Du wurdest zum neuen Präsidenten der Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " ernannt";
                break;
            case ALLIANCE_JOBS_SUCCESSOR:
                if (currentUser()->getId() == $user->getId()) {
                    $this->addInformation("Du kannst Dich nicht selbst befördern");
                    return;
                }
                $this->getAlliance()->setSuccessor($user->getId());
                $text = "Du wurdest zum neuen Vize-Präsidenten der Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " ernannt";
                break;
            case ALLIANCE_JOBS_DIPLOMATIC:
                if (currentUser()->getId() == $user->getId()) {
                    $this->addInformation("Du kannst Dich nicht selbst befördern");
                    return;
                }
                $this->getAlliance()->setDiplomatic($user->getId());
                $text = "Du wurdest zum neuen Außenminister der Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " ernannt";
                break;
        }
        $this->getAlliance()->truncateJobCache();
        PM::sendPM(currentUser()->getId(), $user->getId(), $text);
        $this->addInformation("Das Mitglied wurde befördert");
    }

    function changeAvatar()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $this->setView('EDIT_ALLIANCE');
        $file = $_FILES['avatar'];
        if ($file['type'] != "image/png") {
            $this->addInformation('Es können nur Bilder im PNG-Format hochgeladen werden');
            return;
        }
        if ($file['size'] > 200000) {
            $this->addInformation('Die maximale Dateigröße liegt bei 200 Kilobyte');
            return;
        }
        if ($file['size'] == 0) {
            $this->addInformation('Die Datei ist leer');
            return;
        }
        if ($this->getAlliance()->getAvatar()) {
            @unlink(AVATAR_ALLIANCE_PATH_INTERNAL . $this->getAlliance()->getAvatar() . ".png");
        }
        $imageName = md5($this->getAlliance()->getId() . "_" . time());
        $img = imagecreatefrompng($file['tmp_name']);
        if (imagesx($img) > 600) {
            $this->addInformation(_('Das Bild darf maximal 600 Pixel breit sein'));
            return;
        }
        if (imagesy($img) > 150) {
            $this->addInformation(_('Das Bild darf maximal 150 Pixel hoch sein'));
            return;
        }
        $newImage = imagecreatetruecolor(imagesx($img), imagesy($img));
        imagecopy($newImage, $img, 0, 0, 0, 0, imagesx($img), imagesy($img));
        imagepng($newImage, AVATAR_ALLIANCE_PATH_INTERNAL . $imageName . ".png");
        $this->getAlliance()->setAvatar($imageName);
        $this->getAlliance()->save();
        $this->addInformation("Das Bild wurde erfolgreich hochgeladen");
    }

    /**
     */
    protected function deleteAvatar()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $this->setView('EDIT_ALLIANCE');
        if ($this->getAlliance()->getAvatar()) {
            @unlink(AVATAR_ALLIANCE_PATH_INTERNAL . $this->getAlliance()->getAvatar() . ".png");
            $this->getAlliance()->setAvatar('');
            $this->getAlliance()->save();
        }
        $this->addInformation(_('Das Bild wurde gelöscht'));
    }

    private $boards = null;

    function getBoards()
    {
        if ($this->boards === null) {
            $this->boards = AllianceBoard::getListByAlliance($this->getAlliance()->getId());
        }
        return $this->boards;
    }

    function addBoard()
    {
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $name = strip_tags(request::postString('board'));
        if (strlen($name) < 5) {
            $this->addInformation("Der Name muss mindestens 5 Zeichen lang sein");
            return;
        }
        $this->board = new AllianceBoardData;
        $this->getBoard()->setAllianceId($this->getAlliance()->getId());
        $this->getBoard()->setName(tidyString($name));
        $this->getBoard()->save();

        $this->addInformation("Das Forum wurde erstellt");
    }

    function createAllianceTopic()
    {
        $this->enforceAllianceCheck();
        $name = strip_tags(request::postString('tname'));
        $text = strip_tags(request::postString('ttext'));
        $this->posting = new AlliancePostData;
        $this->getPosting()->setText(tidyString($text));
        $this->getPosting()->setName(tidyString($name));
        if (strlen($this->getPosting()->getName()) < 1) {
            $this->setView("SHOW_NEW_TOPIC");
            $this->addInformation("Es wurde kein Themenname eingegeben");
            return;
        }
        if (strlen($this->getPosting()->getText()) < 1) {
            $this->setView("SHOW_NEW_TOPIC");
            $this->addInformation("Es wurde kein Text eingegeben");
            return;
        }
        $date = time();
        $topic = new AllianceTopicData;
        $topic->setBoardId($this->getBoard()->getId());
        $topic->setAllianceId($this->getAlliance()->getId());
        $topic->setName(tidyString($name));
        $topic->setUserId(currentUser()->getId());
        $topic->setLastPostDate($date);
        $topic->save();
        $this->topic = $topic;

        $this->getPosting()->setBoardId($this->getBoard()->getId());
        $this->getPosting()->setTopicId($topic->getId());
        $this->getPosting()->setAllianceId($this->getAlliance()->getId());
        $this->getPosting()->setUserId(currentUser()->getId());
        $this->getPosting()->setDate($date);
        $this->getPosting()->save();

        $this->setView('SHOW_TOPIC');
        $this->addInformation("Das Thema wurde erstellt");
    }

    function createAlliancePost()
    {
        $this->enforceAllianceCheck();
        $text = strip_tags(request::postString('ttext'));
        $this->posting = new AlliancePostData;
        $this->getPosting()->setText(tidyString($text));
        if (strlen($this->getPosting()->getText()) < 1) {
            $this->setView("SHOW_NEW_POST");
            $this->addInformation("Es wurde kein Text eingegeben");
            return;
        }
        $date = time();
        $this->getPosting()->setBoardId($this->getBoard()->getId());
        $this->getPosting()->setTopicId($this->getTopic()->getId());
        $this->getPosting()->setAllianceId($this->getAlliance()->getId());
        $this->getPosting()->setUserId(currentUser()->getId());
        $this->getPosting()->setDate($date);
        $this->getPosting()->save();

        $this->getTopic()->setLastPostdate($date);
        $this->getTopic()->save();

        $this->setView('SHOW_TOPIC');
        $this->addInformation("Die Antwort wurde erstellt");
    }

    function renameTopic()
    {
        $this->enforceAllianceCheck();
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $name = strip_tags(request::postString('tname'));
        $this->getTopic()->setName(tidyString($name));
        if (strlen($this->getTopic()->getName()) < 1) {
            $this->addInformation("Der Name ist zu kurz");
            return;
        }
        $this->getTopic()->save();
        $this->setView("SHOW_BOARD");
        $this->addInformation("Das Thema wurde umbenannt");
    }

    function deleteTopic()
    {
        $this->enforceAllianceCheck();
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }

        $this->getTopic()->deepDelete();

        $this->addInformation("Das Thema wurde gelöscht");
        $this->setView("SHOW_BOARD");
    }

    function renameBoard()
    {
        $this->enforceAllianceCheck();
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }
        $name = strip_tags(request::postString('tname'));
        $this->getBoard()->setName(tidyString($name));
        if (strlen($this->getBoard()->getName()) < 1) {
            $this->addInformation("Der Name ist zu kurz");
            return;
        }
        $this->getBoard()->save();
        $this->setView("SHOW_BOARDS");
        $this->addInformation("Das Thema wurde umbenannt");
    }

    function deleteBoard()
    {
        $this->enforceAllianceCheck();
        if (!$this->getAlliance()->currentUserMayEdit()) {
            new AccessViolation;
        }

        $this->getBoard()->deepDelete();

        $this->addInformation("Das Forum wurde gelöscht");
        $this->setView("SHOW_BOARDS");
    }

    private $relations = null;

    function getRelations()
    {
        if ($this->relations === null) {
            $result = AllianceRelation::getList('alliance_id=' . $this->getAlliance()->getId() . ' OR recipient=' . $this->getAlliance()->getId());
            $this->relations = array();
            foreach ($result as $key => $obj) {
                if ($obj->getRecipientId() == $this->getAlliance()->getId()) {
                    $obj->cycleOpponents();
                }
                $this->relations[$key] = $obj;
            }
        }
        return $this->relations;
    }

    protected function newRelation()
    {
        if (!$this->getAlliance()->currentUserIsDiplomatic()) {
            new AccessViolation;
        }
        $opp = new \Alliance(request::postIntFatal('oid'));
        $type = request::postIntFatal('type');
        if (!AllianceRelation::isValidRelationType($type)) {
            return;
        }
        if (currentUser()->getAllianceId() == $opp->getId()) {
            return;
        }
        $cnt = AllianceRelation::countInstances('date=0 AND ((alliance_id=' . $this->getAlliance()->getId() . ' AND recipient=' . $opp->getId() . ') OR
					(alliance_id=' . $opp->getId() . ' AND recipient=' . $this->getAlliance()->getId() . '))');
        if ($cnt >= 2) {
            $this->addInformation("Es gibt bereits ein Angebot für diese Allianz");
            return;
        }

        $rel = AllianceRelation::getBy('(alliance_id=' . $this->getAlliance()->getId() . ' AND recipient=' . $opp->getId() . ') OR
						(alliance_id=' . $opp->getId() . ' AND recipient=' . $this->getAlliance()->getId() . ')');
        if ($rel) {
            if ($rel->getType() == $type) {
                return;
            }
            if ($rel->getType() == ALLIANCE_RELATION_WAR && $type != ALLIANCE_RELATION_PEACE) {
                return;
            }
        }
        $obj = new AllianceRelationData;
        $obj->setAllianceId($this->getAlliance()->getId());
        $obj->setRecipientId($opp->getId());
        $obj->setType($type);
        if ($type == ALLIANCE_RELATION_WAR) {
            AllianceRelation::truncateBy('(alliance_id=' . $this->getAlliance()->getId() . ' AND recipient=' . $opp->getId() . ') OR
						(alliance_id=' . $opp->getId() . ' AND recipient=' . $this->getAlliance()->getId() . ')');
            $obj->setDate(time());
            $obj->save();
            $text = "Die Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " hat Deiner Allianz den Krieg erklärt";
            $opp->sendMessage($text);
            HistoryEntry::addAllianceEntry("Die Allianz " . $this->getAlliance()->getName() . " hat der Allianz " . $opp->getName() . " den Krieg erklärt",
                currentUser()->getId());
            $this->addInformation("Der Allianz " . $opp->getNameWithoutMarkup() . " wurde der Krieg erklärt");
            return;
        }
        $obj->save();
        $text = "Die Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " hat Deiner Allianz ein Abkommen angeboten";
        $opp->sendMessage($text);
        $this->addInformation("Das Abkommen wurde angeboten");
    }

    function suggestPeace()
    {
        $relId = request::getIntFatal('al');
        $relation = AllianceRelation::getById($relId);
        $rel = AllianceRelation::getBy('type=' . ALLIANCE_RELATION_PEACE . ' AND ((alliance_id=' . $this->getAlliance()->getId() . ' AND recipient=' . $relation->getOpponent()->getId() . ') OR
						(alliance_id=' . $relation->getOpponent()->getId() . ' AND recipient=' . $this->getAlliance()->getId() . '))');
        if ($rel > 0) {
            $this->addInformation("Der Allianz wird bereits ein Friedensabkommen angeboten");
            return;
        }
        if (!$relation || ($relation->getRecipientId() != $this->getAlliance()->getId() && $relation->getAllianceId() != $this->getAlliance()->getId())) {
            return;
        }
        if ($relation->getType() != ALLIANCE_RELATION_WAR) {
            return;
        }
        $obj = new AllianceRelationData;
        $obj->setAllianceId($this->getAlliance()->getId());
        $obj->setRecipientId($relation->getOpponent()->getId());
        $obj->setType(ALLIANCE_RELATION_PEACE);
        $obj->save();
        $text = "Die Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " hat Deiner Allianz ein Friedensabkommen angeboten";
        $this->sendMessageToOpponent($relation, $text);
        $this->addInformation("Der Frieden wurde angeboten");

    }

    function cancelOffer()
    {
        $relId = request::getIntFatal('al');
        $relation = AllianceRelation::getById($relId);
        if (!$relation || $relation->getAllianceId() != $this->getAlliance()->getId()) {
            return;
        }
        if (!$relation->isPending()) {
            return;
        }
        $relation->deleteFromDatabase();
        $text = "Die Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " hat das Angebot zurückgezogen";
        PM::sendPM(USER_NOONE, $relation->getRecipient()->getFounder()->getUserId(), $text);
        if ($relation->getRecipient()->getDiplomatic()) {
            PM::sendPM(USER_NOONE, $relation->getRecipient()->getDiplomatic()->getUserId(), $text);
        }
        $this->addInformation("Das Angebot wurde zurückgezogen");
    }

    function acceptOffer()
    {
        $relId = request::getIntFatal('al');
        $relation = AllianceRelation::getById($relId);
        if (!$relation || $relation->getRecipientId() != $this->getAlliance()->getId()) {
            return;
        }
        if (!$relation->isPending()) {
            return;
        }
        $rel = AllianceRelation::getBy('date>0 AND ((alliance_id=' . $relation->getAllianceId() . ' AND recipient=' . $relation->getRecipientId() . ') OR
						(alliance_id=' . $relation->getRecipientId() . ' AND recipient=' . $relation->getAllianceId() . '))');
        if ($rel) {
            $rel->deleteFromDatabase();
        }
        $relation->setDate(time());
        $relation->save();
        $text = $relation->getTypeDescription() . " abgeschlossen!\nDie Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " hat hat das Angebot angenommen";
        $this->sendMessageToOpponent($relation, $text);
        HistoryEntry::addAllianceEntry("Die Allianzen " . $relation->getAlliance()->getName() . " und " . $relation->getOpponent()->getName() . " sind ein " . $relation->getTypeDescription() . " eingegangen",
            currentUser()->getId());
        $this->addInformation("Das Angebot wurden angemommen");
    }

    function cancelContract()
    {
        $relId = request::getIntFatal('al');
        $relation = AllianceRelation::getById($relId);
        $relId = request::getIntFatal('al');
        $relation = AllianceRelation::getById($relId);
        if (!$relation || ($relation->getRecipientId() != $this->getAlliance()->getId() && $relation->getAllianceId() != $this->getAlliance()->getId())) {
            return;
        }
        if ($relation->getType() == ALLIANCE_RELATION_WAR) {
            return;
        }
        if ($relation->isPending()) {
            return;
        }
        $relation->deleteFromDatabase();
        $text = "Die Allianz " . $this->getAlliance()->getNameWithoutMarkup() . " hat das " . $relation->getTypeDescription() . " aufgelöst";
        $this->sendMessageToOpponent($relation, $text);
        HistoryEntry::addAllianceEntry("Das " . $relation->getTypeDescription() . " zwischen den Allianzen " . $relation->getAlliance()->getName() . " und " . $relation->getOpponent()->getName() . " wurde aufgelöst",
            currentUser()->getId());
        $this->addInformation("Das Abkommen wurde aufgelöst");
    }

    private function sendMessageToOpponent($relation, $text)
    {
        if ($relation->getAllianceId() == $this->getAlliance()->getId()) {
            $relation->getOpponent()->sendMessage($text);
            return;
        }
        $relation->getAlliance()->sendMessage($text);
    }

    private $realrelations = null;

    function getAllianceRelations()
    {
        if ($this->realrelations === null) {
            $result = AllianceRelation::getList("date>0 AND (recipient=" . $this->getAlliance()->getId() . " OR alliance_id=" . $this->getAlliance()->getId() . ")");
            $this->realrelations = array();
            foreach ($result as $key => $obj) {
                if ($obj->getRecipientId() == $this->getAlliance()->getId()) {
                    $obj->cycleOpponents();
                }
                $this->realrelations[$key] = $obj;
            }
        }
        return $this->realrelations;
    }

    private $replacementVars = null;

    private function getReplacementVars()
    {
        if ($this->replacementVars === null) {
            $this->replacementVars = array();
            $this->replacementVars['$ALLIANCE_HOMEPAGE_LINK'] = '<a href="' . $this->getAlliance()->getHomepageDisplay() . '" target="_blank">' . _('Zur Allianz Homepage') . '</a>';
            $this->replacementVars['$ALLIANCE_BANNER'] = ($this->getAlliance()->getAvatar() ? '<img src="' . $this->getAlliance()->getFullAvatarpath() . '" />' : false);
            $this->replacementVars['$ALLIANCE_PRESIDENT'] = $this->getAlliance()->getFounder()->getUser()->getName();
            $this->replacementVars['$ALLIANCE_VICEPRESIDENT'] = ($this->getAlliance()->getSuccessor() ? $this->getAlliance()->getSuccessor()->getUser()->getName() : _('Unbesetzt'));
            $this->replacementVars['$ALLIANCE_FOREIGNMINISTER'] = ($this->getAlliance()->getDiplomatic() ? $this->getAlliance()->getDiplomatic()->getUser()->getName() : _('Unbesetzt'));
        }
        return $this->replacementVars;
    }

    public function renderAllianceDescription()
    {
        return str_replace(array_keys($this->getReplacementVars()), array_values($this->getReplacementVars()),
            $this->getAlliance()->getDescription());
    }

    private function getPageMark()
    {
        return request::getInt('mark');
    }

    private $topic_nav = null;

    public function getTopicNavigation()
    {
        if ($this->topic_nav === null) {
            $mark = $this->getPageMark();
            if ($mark % ALLIANCEBOARDLIMITER != 0 || $mark < 0) {
                $mark = 0;
            }
            $maxcount = $this->getTopic()->getPostCount();
            $maxpage = ceil($maxcount / ALLIANCEBOARDLIMITER);
            $curpage = floor($mark / ALLIANCEBOARDLIMITER);
            $ret = array();
            if ($curpage != 0) {
                $ret[] = array("page" => "<<", "mark" => 0, "cssclass" => "pages");
                $ret[] = array("page" => "<", "mark" => ($mark - ALLIANCEBOARDLIMITER), "cssclass" => "pages");
            }
            for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
                if ($i > $maxpage || $i < 1) {
                    continue;
                }
                $ret[] = array(
                    "page" => $i,
                    "mark" => ($i * ALLIANCEBOARDLIMITER - ALLIANCEBOARDLIMITER),
                    "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
                );
            }
            if ($curpage + 1 != $maxpage) {
                $ret[] = array("page" => ">", "mark" => ($mark + ALLIANCEBOARDLIMITER), "cssclass" => "pages");
                $ret[] = array(
                    "page" => ">>",
                    "mark" => $maxpage * ALLIANCEBOARDLIMITER - ALLIANCEBOARDLIMITER,
                    "cssclass" => "pages"
                );
            }
            $this->topic_nav = &$ret;
        }
        return $this->topic_nav;
    }

    public function getTopicPostings()
    {
        return $this->getTopic()->getPostings($this->getPageMark());
    }

    protected function deletePosting()
    {
        if (!$this->getAlliance()->currentUserIsBoardModerator()) {
            new AccessViolation;
        }
        $post = new AlliancePost(request::getIntFatal('pid'));
        if ($post->getTopic()->getPostCount() == 1) {
            $this->setView('SHOW_BOARD');
            $post->getTopic()->deepDelete();
            $this->addInformation(_('Das Thema wurde gelöscht'));
            return;
        }
        $this->setView('SHOW_TOPIC');
        $post->deleteFromDatabase();
        $this->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    protected function setSticky()
    {
        if (!$this->getAlliance()->currentUserIsBoardModerator()) {
            new AccessViolation;
        }
        $topic = new AllianceTopic(request::getIntFatal('tid'));
        $topic->setSticky(1);
        $topic->save();

        $this->addInformation(_('Das Thema wurde als wichtig markiert'));
    }

    protected function unsetSticky()
    {
        if (!$this->getAlliance()->currentUserIsBoardModerator()) {
            new AccessViolation;
        }
        $topic = new AllianceTopic(request::getIntFatal('tid'));
        $topic->setSticky(0);
        $topic->save();

        $this->addInformation(_('Das Thema ist nicht mehr wichtig'));
    }
}
