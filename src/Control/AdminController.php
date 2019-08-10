<?php

namespace Stu\Control;

use AccessViolation;
use Tuple;

final class AdminController extends GameController
{

    private $default_tpl = "html/admin.xhtml";

    public function __construct()
    {
        parent::__construct($this->default_tpl, "/ Admin");
        if (!currentUser()->isAdmin()) {
            throw new AccessViolation;
        }
        $this->addNavigationPart(new Tuple("admin.php", 'Admin'));

        $this->addView('CREATE_BUILDPLAN', 'createBuildPlan');

        $this->render($this);
    }

    protected function createBuildPlan()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/adminmacros.xhtml/createbuildplan');
    }

}
