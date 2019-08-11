<?php

namespace Stu\Control;

use request;
use RPGPlotMember;
use Stu\Lib\Session;
use Tuple;
use User;
use UserProfileVisitors;

final class UserProfileController extends GameController
{

    private $default_tpl = "html/userprofile.xhtml";

    function __construct(
        Session $session
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Siedlerprofil");
        $this->addNavigationPart(new Tuple("userprofile.php?uid=" . $this->getProfile()->getId(), "Siedlerprofil"));

        $this->registerProfileView();
    }

    private $profile = null;

    function getProfile()
    {
        if ($this->profile === null) {
            $this->profile = new User(request::getIntFatal('uid'));
        }
        return $this->profile;
    }

    function registerProfileView()
    {
        if ($this->getProfile()->getId() == currentUser()->getId()) {
            return;
        }
        if (UserProfileVisitors::hasVisit($this->getProfile()->getId(), currentUser()->getId())) {
            return;
        }
        UserProfileVisitors::registerVisit($this->getProfile()->getId(), currentUser()->getId());
    }

    private $plots = null;

    function getRPGPlots()
    {
        if ($this->plots === null) {
            $this->plots = RPGPlotMember::getPlotsByUser($this->getProfile()->getId());
        }
        return $this->plots;
    }
}
