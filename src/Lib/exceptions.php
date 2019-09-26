<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Tal\TalPageInterface;

class AccessViolation extends STUException {

	function __construct() {
		$this->setError("This incident will be reported");
		parent::__construct();
	}

}
class InvalidParamException extends STUException {

	function __construct($param="") {
		$this->setError("Invalid param exception: ".$param);
		parent::__construct();
	}

}
class LoginException extends STUException  {

	function __construct($msg="") {
		$this->setError("Login exception: ".$msg."");
		session_destroy();
		if (request::isAjaxRequest()) {
			header('HTTP/1.0 400');
			exit;
		}
		header('Location: /');
		exit;
	}

}

class STUException extends Exception {
	function __construct() {
        // @todo refactor

        global $container;

        $container->get(EntityManagerInterface::class)->rollback();
		if (isCommandLineCall()) {
			print_r($this->getError());
			exit;
		}
		ob_clean();
		$tpl = $container->get(TalPageInterface::class);
		$tpl->setTemplate('html/defaultexception.xhtml');
		$tpl->setVar('THIS',$this);
		$tpl->parse();
		exit;
	}

	private $error = NULL;

	function setError($value) {
		$this->error = $value;
	}

	function getError() {
		return $this->error;
	}
}
