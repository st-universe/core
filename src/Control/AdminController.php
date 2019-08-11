<?php

namespace Stu\Control;

use AccessViolation;
use Stu\Lib\Session;
use Tuple;

final class AdminController extends GameController
{

    private $default_tpl = "html/admin.xhtml";

    public function __construct(
        Session $session
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Admin");
        if (!currentUser()->isAdmin()) {
            throw new AccessViolation;
        }
        $this->addNavigationPart(new Tuple("admin.php", 'Admin'));

        $this->addView('CREATE_BUILDPLAN', 'createBuildPlan');
    }

    protected function createBuildPlan()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/adminmacros.xhtml/createbuildplan');
    }

}
