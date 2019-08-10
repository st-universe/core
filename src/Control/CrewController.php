<?php

namespace Stu\Control;

use AccessViolation;
use Crew;
use request;

final class CrewController extends GameController
{

    private $default_tpl = "";

    function __construct()
    {
        parent::__construct($this->default_tpl, "Crewinfo");

        $this->addCallback('B_CHANGE_NAME', 'changeName');

        $this->addView('SHOW_CREW_DETAILS', 'showCrewDetails');

        $this->render($this);
    }

    private $crew = null;

    public function getCrew()
    {
        if ($this->crew === null) {
            $this->crew = Crew::getById(request::getIntFatal('cid'));
            if (!$this->crew->ownedByCurrentUser()) {
                new AccessViolation;
            }
        }
        return $this->crew;
    }

    protected function changeName()
    {
        $name = stripslashes(request::getStringFatal('name'));
        if (!preg_match('=^[a-zA-Z0-9äöüÄÜÖ\.,\-\'" ]+$=i', $name)) {
            echo $this->getCrew()->getName();
            exit;
        }
        $this->getCrew()->setName(tidyString($name));
        $this->getCrew()->save();
        echo $name;
        exit;
    }

    protected function showCrewDetails()
    {
        $this->setPageTitle("Crewdetails");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/macros.xhtml/crew_details');
    }
}
