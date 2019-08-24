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
        $this->addCallBack("B_ADD_PMCATEGORY", "addPMCategory");
        $this->addCallBack("B_MOVE_PM", "movePMToCategory");
        $this->addCallBack("B_DELETE_PMS", "deleteMarkedPMs");
        $this->addCallBack("B_DELETE_ALL_PMS", "deleteAllPMs", true);
        $this->addCallBack("B_PMCATEGORY_SORT", "changePMCategorySort");
        $this->addCallBack("B_EDIT_PMCATEGORY_NAME", "editPMCategoryName");
        $this->addCallBack("B_DELETE_PMCATEGORY", "deletePMCategory");
        $this->addCallBack("B_IGNORE_USER", "ignoreUser");
        $this->addCallBack("B_ADD_CONTACT", "addToContactlist");
        $this->addCallBack("B_CHANGE_CONTACTMODE", "editContactMode");
        $this->addCallBack("B_DELETE_CONTACTS", "deleteMarkedContacts");
        $this->addCallBack("B_DELETE_ALL_CONTACTS", "deleteAllContacts", true);
        $this->addCallBack("B_DELETE_IGNORES", "deleteMarkedIgnores");
        $this->addCallBack("B_DELETE_ALL_IGNORES", "deleteAllIgnores", true);
        $this->addCallBack("B_CREATE_PLOT", "createRPGPlot");
        $this->addCallBack("B_EDIT_PLOT", "editRPGPlot");
        $this->addCallBack("B_ADD_PLOTMEMBER", "addPlotMember");
        $this->addCallBack("B_DEL_PLOTMEMBER", "delPlotMember", true);
        $this->addCallBack("B_END_PLOT", "endPlot", true);
        $this->addCallBack("B_POST_COMMENT", "postComment");
        $this->addCallBack("B_DELETE_COMMENT", "deleteComment");
        $this->addCallBack('B_EDIT_CONTACT_COMMENT', 'editContactComment');

        $this->addView("WRITE_KN", "writeKN");
        $this->addView("SHOW_IGNORE", "showIgnore");
        $this->addView("SHOW_CONTACT_MODESWITCH", "showContactlistModeswitch");
        $this->addView("SHOW_CONTACT_MODE", "showContactMode");

        $this->addView("SHOW_NOOP", "showNoop");
    }

    function showIgnore()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/macros.xhtml/ignoretext');
    }

    function showAddContact()
    {
        $this->getTemplate()->setVar('clobj', $this->getContactlist());
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/macros.xhtml/contacttext');
    }

    function showContactMode()
    {
        $this->getTemplate()->setVar('contact', $this->contact);
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/commmacros.xhtml/clmodeview');
    }

    function showContactlistModeswitch()
    {
        $contact = new Contactlist(request::getIntFatal('cid'));
        if (!$contact->isOwnContact()) {
            return;
        }
        $this->setPageTitle("Status");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->getTemplate()->setRef('contact', $contact);
        $this->setAjaxMacro('html/commmacros.xhtml/clmodeswitch');
    }

    function ignoreUser()
    {
        $userId = request::indInt('recid');
        $user = User::getUserById($userId);
        if (!$user) {
            $this->addInformation("Dieser Siedler existiert nicht");
            return;
        }
        if ($user->getId() == currentUser()->getId()) {
            $this->addInformation("Du kannst Dich nicht selbst ignorieren");
            return;
        }
        if (Ignorelist::isOnList(currentUser()->getId(), $user->getId()) == 1) {
            $this->addInformation("Der Siedler befindet sich bereits auf Deiner Ignoreliste");
            return false;
        }
        $ignore = new IgnorelistData();
        $ignore->setUserId(currentUser()->getId());
        $ignore->setDate(time());
        $ignore->setRecipientId($user->getId());
        $ignore->save();
        $this->addInformation("Der Siedler wird ignoriert");
    }

    function writeKN()
    {
        $this->setTemplateFile('html/writekn.xhtml');
        $this->addNavigationPart(new Tuple("comm.php", "Kommunikationsnetzwerk"));
        if (request::getInt('knid')) {
            $knid = request::getInt('knid');
            $this->setPageTitle("Beitrag editieren");
            $this->currentposting = new KNPosting($knid);
            $this->addNavigationPart(new Tuple("comm.php?WRITE_KN=1&knid=" . $knid, "Beitrag editieren"));
        } else {
            $this->setPageTitle("Beitrag schreiben");
            $this->addNavigationPart(new Tuple("comm.php?WRITE_KN=1", "Beitrag schreiben"));
        }
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

    function deleteMarkedContacts()
    {
        $msg = request::indArray('deleted');
        foreach ($msg as $key => $val) {
            $contact = Contactlist::getById($val);
            if (!$contact || !$contact->isOwnContact()) {
                continue;
            }
            $contact->deleteFromDatabase();
        }
        $this->addInformation("Die Kontakte wurden gelöscht");
    }

    function deleteAllContacts()
    {
        Contactlist::truncate('WHERE user_id=' . currentUser()->getId());
        $this->addInformation("Die Kontakte wurden gelöscht");
    }

    function deleteMarkedIgnores()
    {
        $msg = request::indArray('deleted');
        foreach ($msg as $key => $val) {
            $contact = Ignorelist::getById($val);
            if (!$contact || !$contact->isOwnIgnore()) {
                continue;
            }
            $contact->deleteFromDatabase();
        }
        $this->addInformation("Die Einträge wurden gelöscht");
    }

    function deleteAllIgnores()
    {
        Ignorelist::truncate('WHERE user_id=' . currentUser()->getId());
        $this->addInformation("Die Einträge wurden gelöscht");
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

    function addPMCategory()
    {
        $this->setView('SHOW_CAT_LIST');
        $name = request::getString('catname');
        if (strlen($name) < 1) {
            return;
        }
        $cat = new PMCategoryData(array());
        $cat->setUserId(currentUser()->getId());
        $cat->appendToSorting();
        $cat->setDescription(tidyString($name));
        $cat->save();
    }

    function editPMCategoryName()
    {
        $this->setView('SHOW_CAT_LIST');
        $name = request::getString('catname');
        if (strlen($name) < 1) {
            return;
        }
        $catid = request::getIntFatal('pmcat');
        $cat = new PMCategory($catid);
        $cat->setDescription(tidyString($name));
        $cat->save();
    }

    function deletePMCategory()
    {
        $this->setView('SHOW_PM_CAT');
        $catid = request::postInt('pmcat');
        $cat = PMCategory::getById($catid);
        if (!$cat || !$cat->isOwnCategory() || !$cat->isDeleteAble()) {
            return;
        }
        $cat->truncate();
        $cat->deleteFromDatabase();
        $this->addInformation("Der Ordner wurde gelöscht");
    }

    function changePMCategorySort()
    {
        $this->setView('SHOW_NOOP');
        $order = request::getArray('catlist');
        $cats = $this->getPMCategories();
        foreach ($order as $key => $value) {
            if (!array_key_exists($value, $cats)) {
                continue;
            }
            $cats[$value]->setSort(intval($key));
            $cats[$value]->save();
        }
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

    private $contactlist = null;

    function getContactlist()
    {
        if ($this->contactlist === null) {
            $this->contactlist = new ContactlistData();
        }
        return $this->contactlist;
    }

    private $rpgplot = null;

    function getRPGPlot()
    {
        if ($this->rpgplot === null) {
            $this->rpgplot = new RPGPlotData();
        }
        return $this->rpgplot;
    }

    private $contact = null;

    function addToContactlist()
    {
        $this->getTemplate()->setVar('div', request::getString('cldiv'));
        $recid = request::indInt('recid');
        $user = User::getUserById($recid);
        if (!$user) {
            $this->addInformation("Dieser Siedler existiert nicht");
            return;
        }
        if (isSystemUser($user->getId())) {
            $this->addInformation(_("Dieser Siedler kann nicht hinzugefügt werden"));
            return;
        }
        if ($user->getId() == currentUser()->getId()) {
            $this->addInformation("Du kannst Dich nicht selbst auf die Kontaktliste setzen");
            return;
        }
        if (Contactlist::isOnList(currentUser()->getId(), $recid) == 1) {
            $this->addInformation("Dieser Siedler befindet sich bereits auf Deiner Kontaktliste");
            return;
        }
        $mode = request::indInt('clmode');
        if (!array_key_exists($mode, getContactlistModes())) {
            return;
        }
        $contact = new ContactlistData;
        $contact->setUserId(currentUser()->getId());
        $contact->setMode($mode);
        $contact->setRecipientId($user->getId());
        $contact->setDate(time());
        $contact->save();
        $this->contact = $contact;
        if ($mode == Contactlist::CONTACT_ENEMY) {
            PM::sendPM(currentUser()->getId(), $user->getId(), "Der Siedler betrachtet Dich von nun an als Feind");
        }
        $this->addInformation("Der Siedler wurde hinzugefügt");
    }

    function editContactMode()
    {
        $this->getTemplate()->setVar('div', request::getString('cldiv'));
        $contactid = request::indInt('cid');
        $contact = Contactlist::getById($contactid);
        if (!$contact || !$contact->isOwnContact()) {
            return;
        }
        $mode = request::indInt('clmode');
        if (!array_key_exists($mode, getContactlistModes())) {
            return;
        }
        if ($mode != $contact->getMode() && $mode == Contactlist::CONTACT_ENEMY) {
            PM::sendPM(currentUser()->getId(), $contact->getRecipientId(),
                _("Der Siedler betrachtet Dich von nun an als Feind"));
            $obj = Contactlist::hasContact($contact->getRecipientId(), currentUser()->getId());
            if ($obj) {
                if (!$obj->isEnemy()) {
                    $obj->setMode(Contactlist::CONTACT_ENEMY);
                    $obj->save();
                }
            } else {
                $obj = new ContactlistData();
                $obj->setUserId($contact->getRecipientId());
                $obj->setRecipientId(currentUser()->getId());
                $obj->setMode(Contactlist::CONTACT_ENEMY);
                $obj->setDate(time());
                $obj->save();
            }
        }
        $contact->setMode($mode);
        $contact->save();
        $this->contact = $contact;
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

    public function getActiveRPGPlots()
    {
        if ($this->rpgplots === null) {
            $this->rpgplots = RPGPlot::getObjectsBy('WHERE end_date=0 AND id IN (SELECT plot_id FROM stu_plots_members WHERE user_id=' . currentUser()->getId() . ') ORDER BY start_date DESC');
        }
        return $this->rpgplots;
    }
}
