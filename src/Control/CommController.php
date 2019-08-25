<?php

namespace Stu\Control;

use PM;
use request;
use RPGPlot;
use RPGPlotData;
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

        $this->addCallBack("B_CREATE_PLOT", "createRPGPlot");
        $this->addCallBack("B_EDIT_PLOT", "editRPGPlot");
        $this->addCallBack("B_ADD_PLOTMEMBER", "addPlotMember");
        $this->addCallBack("B_DEL_PLOTMEMBER", "delPlotMember", true);
        $this->addCallBack("B_END_PLOT", "endPlot", true);

        $this->addView("SHOW_NOOP", "showNoop");
    }

    private $rpgplot = null;

    function getRPGPlot()
    {
        if ($this->rpgplot === null) {
            $this->rpgplot = new RPGPlotData();
        }
        return $this->rpgplot;
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
}
