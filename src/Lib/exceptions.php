<?php

use Stu\Module\Tal\TalPage;

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
class DBException extends STUException  {
	function __construct($msg,$qry=FALSE) {
		$err = "DB exception".$msg;
		if ($qry) $err .= "<br />".$qry;
		$this->setError($err);
		parent::__construct();
	}
}
class ObjectNotFoundException extends STUException  {
	function __construct($obj,$creator="NA") {
		if (DEBUG_MODE || (function_exists('currentUser') && isAdmin(currentUser()->getId()))) {
			$this->setError("Object not found exception - Object: ".$obj."; call: ".$creator);
			printBackTrace();
		} else {
			$this->setError("Object not found exception");
		}
		parent::__construct();
	}
}

class STUException extends Exception {
	function __construct() {
		DB()->rollbackTransaction();
		if (isCommandLineCall()) {
			print_r($this->getError());
			exit;
		}
		ob_clean();
		$tpl = new TalPage('html/defaultexception.xhtml');
		$tpl->setVar('THIS',$this);
		$tpl->parse();
		if (!$this instanceof DBException && currentUser() && isAdmin(currentUser()->getId())) {
			printBacktrace();
		}
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

class ErrorCollector {

	private $errors = array();

	function __construct() {
	}

	function addDebugNotice(&$note) {
		$this->errors[] = array('cssclass' => 'debug','msg' => $note,'file' => '','line' => 0);
	}

	function getDebugNotices() {
		return $this->errors;
	}

	function addErrorNotice(&$note,$file='',$line='') {
		$this->errors[] = array('cssclass' => 'error','msg' => $note,'file' => $file,'line' => $line);
	}

}
