<?php
class request {

	function getvars() {
		global $_GET;
		return $_GET;
	}

	function postvars() {
		global $_POST;
		return $_POST;
	}

	public function isPost(): bool {
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	public function has(string $key): bool {
		return self::getvars()[$key] ?? self::postvars()[$key] ?? false;
	}

	function getVarByMethod(&$method,&$var,$fatal=false) {
		if (!@array_key_exists($var,$method)) {
			if ($fatal === true) {
				new InvalidParamException($var);
			}
			return FALSE;
		}
		return $method[$var];
	}	

	function getInt($var,$std=0) {
		$int = self::getVarByMethod(self::getvars(),$var);
		if (strlen($int) == 0) {
			return $std;
		}
		return self::returnInt($int);
	}

	function getIntFatal($var) {
		$int = self::getVarByMethod(self::getvars(),$var,true);
		return self::returnInt($int);
	}

	function postInt($var) {
		$int = self::getVarByMethod(self::postvars(),$var);
		return self::returnInt($int);
	}

	function postIntFatal($var) {
		$int = self::getVarByMethod(self::postvars(),$var,true);
		return self::returnInt($int);
	}

	function getString($var) {
		return self::getVarByMethod(self::getvars(),$var);
	}
	
	function postString($var) {
		return self::getVarByMethod(self::postvars(),$var);
	}

	function indString($var) {
		$value = self::getVarByMethod(self::postvars(),$var);
		if ($value) {
			return $value;
		}
		return self::getVarByMethod(self::getvars(),$var);
	}
	
	function indInt($var) {
		$value = self::getVarByMethod(self::postvars(),$var);
		if ($value) {
			return self::returnInt($value);
		}
		return self::returnInt(self::getVarByMethod(self::getvars(),$var));
	}

	function indArray($var) {
		$value = self::getVarByMethod(self::postvars(),$var);
		if ($value) {
			return self::returnArray($value);
		}
		return self::returnArray(self::getVarByMethod(self::getvars(),$var));
	}

	function postStringFatal($var) {
		return self::getVarByMethod(self::postvars(),$var,true);
	}	
	
	function getStringFatal($var) {
		return self::getVarByMethod(self::getvars(),$var,true);
	}

	function getArrayFatal($var) {
		return self::returnArray(self::getVarByMethod(self::getvars(),$var,true));
	}

	function postArrayFatal($var) {
		return self::returnArray(self::getVarByMethod(self::postvars(),$var,true));
	}

	function getArray($var) {
		return self::returnArray(self::getVarByMethod(self::getvars(),$var));
	}

	function postArray($var) {
		return self::returnArray(self::getVarByMethod(self::postvars(),$var));
	}

	function returnInt(&$result) {
		if (!$result || $result < 0) {
			return 0;
		}
		return intval($result);
	}

	function returnArray(&$result) {
		if (!is_array($result)) {
			return array();
		}
		return $result;
	}

	function setVar($var,$value) {
		global $_GET,$_POST;
		$_GET[$var] = $value;
		$_POST[$var] = $value;
	}
	
	function delVar($var) {
		global $_GET,$_POST;
		unset($_GET[$var]);
		unset($_POST[$var]);
	}

	/**
	 */
	static function isAjaxRequest() { #{{{
		if (self::indInt('ajax') == 1) {
			return TRUE;
		}
		return FALSE;
	} # }}}

}
