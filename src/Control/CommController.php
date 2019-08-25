<?php

namespace Stu\Control;

use Contactlist;
use KnComment;
use KnCommentData;
use KNPosting;
use KNPostingData;
use ObjectNotFoundException;
use PM;
use PMCategory;
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

        $this->addCallBack("B_EDIT_KN", "editKNPosting");
        $this->addCallBack("B_DEL_KN", "delKNPosting", true);
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
}
