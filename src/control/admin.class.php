<?php

/*
 *
 * Copyright 2011 Daniel Jakob All Rights Reserved
 * This software is the proprietary information of Daniel Jakob
 * Use is subject to license terms
 *
 */

/**
 * @author Daniel Jakob <wolverine@stuniverse.de>
 * @access public
 */
class AdminApp extends gameapp { #{{{
	
	private $default_tpl = "html/admin.xhtml";

	function __construct() {
		parent::__construct($this->default_tpl,"/ Admin");
		if (!currentUser()->isAdmin()) {
			throw new AccessViolation;
		}
		$this->addNavigationPart(new Tuple("admin.php",'Admin'));

		$this->addView('CREATE_BUILDPLAN','createBuildPlan');

		$this->render($this);
	}

	/**
	 */
	protected function createBuildPlan() { #{{{
		$this->setTemplateFile('html/ajaxempty.xhtml');
		$this->setAjaxMacro('html/adminmacros.xhtml/createbuildplan');
	} # }}}

	
} #}}}
?>
