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

    private const KNLIMITER = 6;
    public const PMLIMITER = 6;

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
        $this->addView("SHOW_INBOX", "showInbox");
        $this->addView("SHOW_OUTBOX", "showOutbox");
        $this->addView("SHOW_PM_CAT", "showPMCat");
        $this->addView("SHOW_NEW_CAT", "showNewCategory");
        $this->addView("SHOW_CAT_LIST", "showCategoryList");
        $this->addView("SHOW_EDIT_CAT", "showEditCategory");
        $this->addView("SHOW_IGNORE", "showIgnore");
        $this->addView("SHOW_CONTACTLIST", "showContactlist");
        $this->addView("SHOW_IGNORELIST", "showIgnorelist");
        $this->addView("SHOW_CONTACT_MODESWITCH", "showContactlistModeswitch");
        $this->addView("SHOW_CONTACT_MODE", "showContactMode");
        $this->addView("SHOW_CREATE_PLOT", "showCreatePlot");
        $this->addView("SHOW_EDIT_PLOT", "showEditPlot");
        $this->addView("SHOW_PLOTLIST", "showPlotlist");
        $this->addView("SHOW_MYPLOTS", "showUserPlotlist");
        $this->addView("SHOW_PLOT", "showPlotDetails");
        $this->addView("SHOW_PLOTKN", "showPlotKn");
        $this->addView("SHOW_KN_COMMENTS", "showKnComments");
        $this->addView("SHOW_KN_COMMENTLIST", "showKnCommentList");

        $this->addView("SHOW_NOOP", "showNoop");
    }

    function showPlotKN()
    {
        $plot = new RPGPlot(request::indInt('plotid'));
        $this->setTemplateFile('html/plotkn.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_PLOTLIST=1", "Plotliste"));
        $this->addNavigationPart(new Tuple("comm.php?SHOW_PLOT=1&plotid=" . $plot->getId(),
            "Plot: " . $plot->getTitleDecoded()));
        $this->addNavigationPart(new Tuple("comm.php?SHOW_PLOTKN=1&plotid=" . $plot->getId(),
            "Plot: " . $plot->getTitleDecoded()));
        $this->setPageTitle("Plot: " . $plot->getTitleDecoded());
    }

    function showContactlist()
    {
        $this->setTemplateFile('html/contactlist.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_CONTACTLIST=1", "Kontaktliste"));
        $this->setPageTitle("Kontaktliste");
    }

    function showIgnorelist()
    {
        $this->setTemplateFile('html/ignorelist.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_IGNORELIST=1", "Ignoreliste"));
        $this->setPageTitle("Ignoreliste");
    }

    function showCreatePlot()
    {
        $this->setTemplateFile('html/createplot.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_CREATE_PLOT=1", "Plot erstellen"));
        $this->setPageTitle("Plot erstellen");
    }

    function showEditPlot()
    {
        $this->rpgplot = new RPGPlot(request::indInt('plotid'));
        if (!$this->getRPGPlot()->ownedByCurrentUser()) {
            return;
        }
        $this->setTemplateFile('html/createplot.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_EDIT_PLOT=1", "Plot editieren"));
        $this->setPageTitle("Plot editieren");
    }

    function showPlotlist()
    {
        $this->setTemplateFile('html/plotlist.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_PLOTLIST=1", "Plotliste"));
        $this->setPageTitle("Plotliste");
    }

    function showUserPlotlist()
    {
        $this->setTemplateFile('html/userplotlist.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_MYPlOTS=1", "Eigene Plots"));
        $this->setPageTitle("Plotliste");
    }

    function showPlotDetails()
    {
        $this->rpgplot = new RPGPlot(request::indInt('plotid'));
        $this->setTemplateFile('html/plotdetails.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_PLOTLIST=1", "Plotliste"));
        $this->addNavigationPart(new Tuple("comm.php?SHOW_PLOT=1&plotid=" . $this->getRPGPlot()->getId(),
            "Plot: " . $this->getRPGPlot()->getTitleDecoded()));
        $this->setPageTitle("Plot: " . $this->getRPGPlot()->getTitleDecoded());
    }

    function showInbox()
    {
        $this->showPMCat();
    }

    function showOutbox()
    {
        request::setVar('pmcat',
            PMCategory::getOrGenSpecialCategory(PM_SPECIAL_PMOUT, currentUser()->getId())->getId());
        $this->showPMCat();
    }

    function showPMCat()
    {
        $this->setTemplateFile('html/pmcategory.xhtml');
        $this->addNavigationPart(new Tuple("comm.php?SHOW_PM_CAT=1&pmcat=" . $this->getPMCategory()->getId(),
            "Private Nachrichten: " . $this->getPMCategory()->getDescriptionDecoded()));
        $this->setPageTitle("Ordner " . $this->getPMCategory()->getDescriptionDecoded());
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

    function showCategoryList()
    {
        $val = true;
        $this->getTemplate()->setVar('markcat', $val);
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/commmacros.xhtml/pmcategorylist_ajax');
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

    function showNewCategory()
    {
        $this->setPageTitle("Ordner anlegen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/commmacros.xhtml/newcategory');
    }

    function showEditCategory()
    {
        $this->setPageTitle("Ordner editieren");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/commmacros.xhtml/editcategory');
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

    function getPlotKNNavigation()
    {
        if ($this->knnav === null) {
            $mark = request::getInt('mark');
            if ($mark % static::KNLIMITER != 0 || $mark < 0) {
                $mark = 0;
            }
            $maxcount = $this->getPlotKNPostingCount();
            $maxpage = ceil($maxcount / static::KNLIMITER);
            $curpage = floor($mark / static::KNLIMITER);
            $ret = array();
            if ($curpage != 0) {
                $ret[] = array("page" => "<<", "mark" => 0, "cssclass" => "pages");
                $ret[] = array("page" => "<", "mark" => ($mark - static::KNLIMITER), "cssclass" => "pages");
            }
            for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
                if ($i > $maxpage || $i < 1) {
                    continue;
                }
                $ret[] = array(
                    "page" => $i,
                    "mark" => ($i * static::KNLIMITER - static::KNLIMITER),
                    "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
                );
            }
            if ($curpage + 1 != $maxpage) {
                $ret[] = array("page" => ">", "mark" => ($mark + static::KNLIMITER), "cssclass" => "pages");
                $ret[] = array("page" => ">>", "mark" => $maxpage * static::KNLIMITER - static::KNLIMITER, "cssclass" => "pages");
            }
            $this->knnav = $ret;
        }
        return $this->knnav;
    }

    private $pmnav = null;

    function getPMNavigation()
    {
        if ($this->pmnav === null) {
            $mark = $this->getPMMark();
            if ($mark % static::PMLIMITER != 0 || $mark < 0) {
                $mark = 0;
            }
            $maxcount = $this->getPMCategory()->getCategoryCount();
            $maxpage = ceil($maxcount / static::PMLIMITER);
            $curpage = floor($mark / static::PMLIMITER);
            $ret = array();
            if ($curpage != 0) {
                $ret[] = array("page" => "<<", "mark" => 0, "cssclass" => "pages");
                $ret[] = array("page" => "<", "mark" => ($mark - static::PMLIMITER), "cssclass" => "pages");
            }
            for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
                if ($i > $maxpage || $i < 1) {
                    continue;
                }
                $ret[] = array(
                    "page" => $i,
                    "mark" => ($i * static::PMLIMITER - static::PMLIMITER),
                    "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
                );
            }
            if ($curpage + 1 != $maxpage) {
                $ret[] = array("page" => ">", "mark" => ($mark + static::PMLIMITER), "cssclass" => "pages");
                $ret[] = array("page" => ">>", "mark" => $maxpage * static::PMLIMITER - static::PMLIMITER, "cssclass" => "pages");
            }
            $this->pmnav = $ret;
        }
        return $this->pmnav;
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
    protected function showKnComments()
    {
        if ($this->currentposting === null) {
            $this->currentposting = new KNPosting(request::getIntFatal('posting'));
        }
        $this->setPageTitle(_("Kommentare für Posting ") . $this->getKNPosting()->getId());
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/commmacros.xhtml/kncomments');
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

    private $knmaxpostingcount;

    private function getKNPostingCount()
    {
        if ($this->knmaxpostingcount === null) {
            $this->knmaxpostingcount = KNPosting::countInstances('1=1');
        }
        return $this->knmaxpostingcount;
    }

    private $knpostings = null;

    public function getKNPostings()
    {
        if ($this->knpostings === null) {
            $this->knpostings = KNPosting::getBy("ORDER BY date DESC LIMIT " . ($this->getKNMark()) . "," . static::KNLIMITER);
        }
        return $this->knpostings;
    }

    public function getPlotPostings()
    {
        if ($this->knpostings === null) {
            $this->knpostings = KNPosting::getBy("WHERE plot_id=" . request::getIntFatal('plotid') . " ORDER BY date DESC LIMIT " . ($this->getKNMark()) . "," . static::KNLIMITER);
        }
        return $this->knpostings;
    }

    public function getPlotKNPostingCount()
    {
        if ($this->knmaxpostingcount === null) {
            $this->knmaxpostingcount = KNPosting::countInstances("plot_id=" . request::getIntFatal('plotid'), 1);
        }
        return $this->knmaxpostingcount;
    }

    public function getKNMark()
    {
        $mark = request::getInt('mark');
        return $mark;
    }

    public function getKNUserMark()
    {
        $mark = DB()->query("SELECT COUNT(id) FROM stu_kn WHERE id>" . currentUser()->getKNMark(), 1);
        return floor($mark / static::KNLIMITER) * static::KNLIMITER;
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

    private $pms = null;

    public function getPMsByCategory()
    {
        if ($this->pms === null) {
            $this->pms = PM::getPMsBy($this->getPMCategory()->getId(), $this->getPMMark());
        }
        return $this->pms;
    }

    private function getPMMark()
    {
        return request::getInt('mark');
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

    public function getFullContactlist()
    {
        if ($this->contactlist === null) {
            $this->contactlist = Contactlist::getList(currentUser()->getId());
        }
        return $this->contactlist;
    }

    private $remotecontacts = null;

    public function getRemoteContacts()
    {
        if ($this->remotecontacts === null) {
            $this->remotecontacts = Contactlist::getRemoteContacts(currentUser()->getId());
        }
        return $this->remotecontacts;
    }

    private $ignorelist = null;

    public function getFullIgnorelist()
    {
        if ($this->ignorelist === null) {
            $this->ignorelist = Ignorelist::getList(currentUser()->getId());
        }
        return $this->ignorelist;
    }

    private $remoteignores = null;

    public function getRemoteIgnores()
    {
        if ($this->remoteignores === null) {
            $this->remoteignores = Ignorelist::getRemoteIgnores(currentUser()->getId());
        }
        return $this->remoteignores;
    }

    private $rpgplots = null;

    public function getRPGPlots()
    {
        if ($this->rpgplots === null) {
            $this->rpgplots = RPGPlot::getObjectsBy('ORDER BY start_date DESC');
        }
        return $this->rpgplots;
    }

    public function getOwnRPGPlots()
    {
        if ($this->rpgplots === null) {
            $this->rpgplots = RPGPlot::getObjectsBy('WHERE id IN (SELECT plot_id FROM stu_plots_members WHERE user_id=' . currentUser()->getId() . ') ORDER BY start_date DESC');
        }
        return $this->rpgplots;
    }

    public function getActiveRPGPlots()
    {
        if ($this->rpgplots === null) {
            $this->rpgplots = RPGPlot::getObjectsBy('WHERE end_date=0 AND id IN (SELECT plot_id FROM stu_plots_members WHERE user_id=' . currentUser()->getId() . ') ORDER BY start_date DESC');
        }
        return $this->rpgplots;
    }
}
