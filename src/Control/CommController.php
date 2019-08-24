<?php

namespace Stu\Control;

use Contactlist;
use ContactlistData;
use Ignorelist;
use IgnorelistData;
use KnComment;
use KnCommentData;
use KNPosting;
use KNPostingData;
use ObjectNotFoundException;
use PM;
use PMCategory;
use PMCategoryData;
use PMData;
use request;
use RPGPlot;
use RPGPlotData;
use RPGPlotMember;
use RPGPlotMemberData;
use Stu\Lib\SessionInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Tuple;
use User;

final class CommController extends GameController
{

    private $default_tpl = "html/comm.xhtml";

    public function __construct(
        SessionInterface $session,
        SessionStringRepositoryInterface $sessionStringRepository
    ) {
        parent::__construct(
            $session,
            $sessionStringRepository,
            $this->default_tpl,
            "/ Kommunikationsnetzwerk"
        );
        $this->addNavigationPart(new Tuple("comm.php", "Kommunikationsnetzwerk"));

        $this->addCallBack("B_WRITE_KN", "addKNPosting", true);
        $this->addCallBack("B_WRITE_PM", "addPM", true);
        $this->addCallBack("B_EDIT_KN", "editKNPosting");
        $this->addCallBack("B_DEL_KN", "delKNPosting", true);
        $this->addCallBack("B_MOVE_PM", "movePMToCategory");
        $this->addCallBack("B_DELETE_PMS", "deleteMarkedPMs");
        $this->addCallBack("B_DELETE_ALL_PMS", "deleteAllPMs", true);
        $this->addCallBack("B_CREATE_PLOT", "createRPGPlot");
        $this->addCallBack("B_EDIT_PLOT", "editRPGPlot");
        $this->addCallBack("B_ADD_PLOTMEMBER", "addPlotMember");
        $this->addCallBack("B_DEL_PLOTMEMBER", "delPlotMember", true);
        $this->addCallBack("B_END_PLOT", "endPlot", true);
        $this->addCallBack("B_POST_COMMENT", "postComment");
        $this->addCallBack("B_DELETE_COMMENT", "deleteComment");
        $this->addCallBack('B_EDIT_CONTACT_COMMENT', 'editContactComment');

        $this->addView("SHOW_NOOP", "showNoop");
    }

    public function getSelectedRecipient()
    {
        return ResourceCache()->getObject("user", request::getIntFatal('recipient'));
    }

    function deleteMarkedPMs()
    {
        $msg = request::indArray('deleted');
        foreach ($msg as $key => $val) {
            $pm = PM::getPMById($val);
            if (!$pm || !$pm->isOwnPM()) {
                continue;
            }
            $pm->deleteFromDatabase();
        }
        $this->addInformation("Die Nachrichten wurden gelöscht");
    }

    function deleteAllPMs()
    {
        $cat = PMCategory::getById(request::getIntFatal('pmcat'));
        if (!$cat || !$cat->isOwnCategory()) {
            return;
        }
        $cat->truncate();
        $this->addInformation("Der Ordner wurden geleert");
    }

    function movePMToCategory()
    {
        if ($this->getPMCategory()->isPMOutDir()) {
            return;
        }
        $pm = PM::getPMById(request::postIntFatal('move_pm'));
        $cat = PMCategory::getById(request::postIntFatal('movecat_' . $pm->getId()));
        if (!$cat || !$cat->isOwnCategory()) {
            $this->addInformation("Dieser Ordner existiert nicht");
            return;
        }
        if (!$pm || !$pm->isOwnPM()) {
            $this->addInformation("Diese Nachricht existiert nicht");
            return;
        }
        $pm->setCategoryId($cat->getId());
        $pm->save();
        $this->addInformation("Die Nachricht wurde verscheben");
    }

    function addKNPosting()
    {
        $title = request::postString('title');
        $text = request::postString('text');
        $plotid = request::postInt('plotid');
        if ($plotid > 0) {
            $plot = RPGPlot::getById($plotid);
            if ($plot && RPGPlotMember::mayWriteStory($plot->getId(), currentUser()->getId())) {
                $this->getKNPosting()->setPlotId($plot->getId());
                $this->getKNPosting()->setTitle($plot->getTitleDecoded());
            }
        } else {
            $this->getKNPosting()->setTitle(tidyString($title));
        }
        $this->getKNPosting()->setSetKNMark(request::postInt('markposting'));
        $this->getKNPosting()->setText(strip_tags(tidyString($text)));

        if (strlen(trim($title)) < 10 && !$plotid) {
            $this->addInformation(_('Der Titel ist zu kurz (mindestens 10 Zeichen)'));
            return;
        }
        if (strlen(trim($text)) < 50) {
            $this->addInformation(_('Der Text ist zu kurz (mindestens 50 Zeichen)'));
            return;
        }
        $this->getKNPosting()->setUserId(currentUser()->getId());
        $this->getKNPosting()->setDate(time());

        $this->getKNPosting()->save();
        $this->addInformation("Der Beitrag wurde hinzugefügt");
        if ($this->getKNPosting()->getSetKNMark()) {
            currentUser()->setKNMark($this->getKNPosting()->getId());
            currentUser()->save();
        }
        request::delVar("WRITE_KN");
    }

    function addPM()
    {
        $text = request::postString('text');
        $recid = request::postInt('recipient');
        $this->getPM()->setText(strip_tags(tidyString($text)));
        $this->getPM()->setRecipientId($recid);
        if (!$recid) {
            $this->addInformation("Es wurde kein Empfänger angegeben");
            return;
        }
        $rec = User::getUserById($recid);
        if (!$rec) {
            $this->addInformation("Dieser Siedler existiert nicht");
            return;
        }
        if ($rec->getId() == currentUser()->getId()) {
            $this->addInformation("Du kannst keine Nachricht an Dich selbst schreiben");
            return;
        }
        if ($rec->isOnIgnoreList(currentUser()->getId())) {
            $this->addInformation("Der Siedler ignoriert Dich");
            return;
        }

        if (strlen($text) < 5) {
            $this->addInformation("Der Text ist zu kurz");
            return;
        }
        $this->getPM()->setSenderId(currentUser()->getId());
        $this->getPM()->setDate(time());
        $cat = PMCategory::getOrGenSpecialCategory(PM_SPECIAL_MAIN, $rec->getId());
        $this->getPM()->setCategoryId($cat->getId());

        $this->getPM()->copyPM();
        $this->getPM()->save();

        if ($this->getReply()) {
            $this->getReply()->setReplied(1);
            $this->getReply()->save();
        }

        $this->addInformation("Die Nachricht wurde abgeschickt");
        request::delVar("WRITE_PM");
        $this->setView("SHOW_PM_CAT", 1);
    }

    function editKNPosting()
    {
        $this->currentposting = new KNPosting(request::postIntFatal('knid'));
        $title = request::postString('title');
        $text = request::postString('text');
        $plotid = request::postInt('plotid');
        if ($plotid > 0) {
            $plot = RPGPlot::getById($plotid);
            if ($plot && RPGPlotMember::mayWriteStory($plot->getId(), currentUser()->getId())) {
                $this->getKNPosting()->setPlotId($plot->getId());
                $this->getKNPosting()->setTitle($plot->getTitleDecoded());
            }
        } else {
            if ($this->getKNPosting()->hasPlot()) {
                $this->getKNPosting()->setPlotId(0);
            }
            $this->getKNPosting()->setTitle(tidyString($title));
        }
        if ($this->getKNPosting()->getUserId() != currentUser()->getId()) {
            new ObjectNotFoundException();
        }
        if (!$this->getKNPosting()->isEditAble()) {
            $this->addInformation("Dieser Beitrag kann nicht editiert werden");
            return;
        }
        $this->getKNPosting()->setText(strip_tags(tidyString($text)));
        $this->getKNPosting()->setEditDate(time());
        if (strlen($text) < 10) {
            $this->addInformation("Der Text ist zu kurz");
            return;
        }
        $this->getKNPosting()->save();
        $this->addInformation("Der Beitrag wurde editiert");
    }

    /**
     */
    protected function delKNPosting()
    {
        $this->currentposting = new KNPosting(request::getIntFatal('knid'));
        if ($this->getKNPosting()->getUserId() != currentUser()->getId()) {
            new ObjectNotFoundException();
        }
        if (!$this->getKNPosting()->isEditAble()) {
            $this->addInformation(_("Dieser Beitrag kann nicht gelöscht werden"));
            return;
        }
        KnComment::truncate('WHERE post_id=' . $this->getKNPosting()->getId());
        $this->getKNPosting()->deleteFromDatabase();
        $this->addInformation(_("Der Beitrag wurde gelöscht"));
    }

    private $currentposting = null;

    function getKNPosting()
    {
        if ($this->currentposting === null) {
            $this->currentposting = new KNPostingData();
        }
        return $this->currentposting;
    }

    private $currentpm = null;

    function getPM()
    {
        if ($this->currentpm === null) {
            $this->currentpm = new PMData();
        }
        return $this->currentpm;
    }

    private $rpgplot = null;

    function getRPGPlot()
    {
        if ($this->rpgplot === null) {
            $this->rpgplot = new RPGPlotData();
        }
        return $this->rpgplot;
    }


    /**
     */
    protected function editContactComment()
    {
        $contactid = request::postIntFatal('edit_contact');
        $contact = Contactlist::getById($contactid);
        if (!$contact || !$contact->isOwnContact()) {
            return;
        }
        $comment = request::postString('comment_' . $contact->getId());
        $value = tidyString(strip_tags($comment));
        $contact->setComment($value);
        $contact->save();
        $this->addInformation(_("Kommentar wurde editiert"));
    }


    function createRPGPlot()
    {
        $title = request::postString('title');
        $description = request::postString('description');
        $title = strip_tags($title);
        $description = strip_tags($description);
        $this->getRPGPlot()->setTitle(tidyString($title));
        $this->getRPGPlot()->setDescription(tidyString($description));
        if (strlen($title) < 6) {
            $this->addInformation("Der Titel ist zu kurz (mindestens 6 Zeichen)");
            return;
        }
        $this->getRPGPlot()->setUserId(currentUser()->getId());
        $this->getRPGPlot()->setStartDate(time());
        $this->getRPGPlot()->save();
        $member = new RPGPlotMemberData();
        $member->setUserId(currentUser()->getId());
        $member->setPlotId($this->getRPGPlot()->getId());
        $member->save();
        $this->addInformation("Der Plot wurde erstellt");
        request::delVar("SHOW_CREATE_PLOT");
        $this->setView("SHOW_PLOTLIST");
    }

    function addPlotMember()
    {
        $plot = new RPGPlot(request::postIntFatal('plotid'));
        if (!$plot->ownedByCurrentUser() || !$plot->isActive()) {
            return;
        }
        $userId = request::postInt('memid');
        $user = User::getUserById($userId);
        if (!$user) {
            $this->addInformation("Dieser Siedler existiert nicht");
            return;
        }
        if ($plot->getUserId() == $user->getId()) {
            $this->addInformation("Du kannst Dich nicht selbst hinzufügen");
            return;
        }
        if (RPGPlot::checkUserPlot($userId, $plot->getId())) {
            $this->addInformation("Dieser Siedler schreibt bereits an diesem Plot");
            return;
        }
        RPGPlot::addPlotMember($userId, $plot->getId());
        PM::sendPM(currentUser()->getId(), $userId,
            "Du wurdest dem RPG-Plot '" . $plot->getTitleDecoded() . "' als Schreiber hinzugefügt");
        $this->addInformation("Der Siedler wurde hinzugefügt");
    }

    function delPlotMember()
    {
        $plot = new RPGPlot(request::getIntFatal('plotid'));
        if (!$plot->ownedByCurrentUser() || !$plot->isActive()) {
            return;
        }
        $userId = request::getInt('memid');
        if ($plot->getUserId() == $userId) {
            $this->addInformation("Du kannst Dich nicht selbst entfernen");
            return;
        }
        if (!RPGPlot::checkUserPlot($userId, $plot->getId())) {
            return;
        }
        RPGPlot::delPlotMember($userId, $plot->getId());
        $this->addInformation("Der Siedler wurde entfernt");
    }

    function endPlot()
    {
        $plot = new RPGPlot(request::postIntFatal('plotid'));
        if (!$plot->ownedByCurrentUser()) {
            return;
        }
        if (!$plot->isActive()) {
            return;
        }
        $plot->setEndDate(time());
        $plot->save();
        $this->addInformation("Der Plot wurde beendet");

    }

    function editRPGPlot()
    {
        $this->rpgplot = new RPGPlot(request::postIntFatal('plotid'));
        if (!$this->getRPGPlot()->ownedByCurrentUser()) {
            return;
        }
        $title = request::postString('title');
        $description = request::postString('description');
        $title = strip_tags($title);
        $description = strip_tags($description);
        $this->getRPGPlot()->setTitle(tidyString($title));
        $this->getRPGPlot()->setDescription(tidyString($description));
        if (strlen($title) < 6) {
            $this->addInformation("Der Titel ist zu kurz (mindestens 6 Zeichen)");
            return;
        }
        $this->getRPGPlot()->save();
        $this->addInformation("Der Plot wurde editiert");
        request::delVar("SHOW_EDIT_PLOT");
        $this->setView("SHOW_PLOT");
    }

    /**
     */
    protected function postComment()
    {
        $this->setView("SHOW_KN_COMMENTS");
        $this->currentposting = new KNPosting(request::getIntFatal('posting'));
        $comment = strip_tags(request::getString('comment'));
        if (strlen($comment) < 3) {
            return;
        }
        $obj = new KnCommentData;
        $obj->setUserId(currentUser()->getId());
        $obj->setDate(time());
        $obj->setPostId($this->getKNPosting()->getId());
        $obj->setText(encodeString(tidyString($comment)));
        $obj->save();
    }

    /**
     */
    protected function deleteComment()
    {
        $this->setView("SHOW_KN_COMMENTS");
        $obj = new KnComment(request::getIntFatal('comment'));
        if ($obj->getPosting()->currentUserMayDeleteComment()) {
            $obj->deleteFromDatabase();
        }
        $this->currentposting = $obj->getPosting();
    }

    private $cattree = null;

    public function getPMCategories()
    {
        if ($this->cattree === null) {
            $this->cattree = PMCategory::getCategoryTree();
        }
        return $this->cattree;
    }

    private $pmcategory = null;

    public function getPMCategory()
    {
        if ($this->pmcategory === null) {
            $cat = request::indInt('pmcat');
            $cats = $this->getPMCategories();
            if (!$cat || !array_key_exists($cat, $cats)) {
                $this->pmcategory = PMCategory::getOrGenSpecialCategory(PM_SPECIAL_MAIN, currentUser()->getId());
            } else {
                $this->pmcategory = $cats[$cat];
            }
        }
        return $this->pmcategory;
    }

    private $pmreply = null;

    public function getReply()
    {
        if ($this->pmreply === null) {
            $this->pmreply = $this->checkPMReply();
        }
        return $this->pmreply;
    }

    private function checkPMReply()
    {
        $repid = request::indInt('reply');
        $pm = PM::getPMById($repid);
        if (!$pm || $pm->getRecipientId() != currentUser()->getId()) {
            return false;
        }
        return $pm;
    }
}
