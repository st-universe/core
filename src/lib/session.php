<?php

class session {

	private $session = NULL;
	private $user = NULL;
	private $loginCheck = TRUE;

	function __construct() {
		if ($this->hasLoginVars()) {
			$this->destroyLoginCookies();
			$this->login();
		}
		if (!$this->isLoggedIn() && !$this->hasLoginVars() && $this->getLoginCheck()) {
			new LoginException("Session abgelaufen");
			return; 
		}
		$sess = $this->getSession();
		if (!$this->hasLoginVars() && $this->getLoginCheck() && (!$sess['uid'] || !$sess['login'])) {
			$this->logout();
			return;
		}
		if ($this->isLoggedIn() && $this->getLoginCheck()) {
			$this->chklogin();
		}
	}

	function getLoginCheck() {
		return $this->loginCheck;
	}

	function setLoginCheck($value) {
		$this->loginCheck = $value;
	}

	function checkLoginCookie() {
		$sstr = $_COOKIE['sstr'];
		$uid = intval($_COOKIE['uid']);
		if ($uid > 0) {
			return $this->performCookieLogin($uid,$sstr);
		}
		return FALSE;
	}

	function isLoggedIn() {
		$sess = $this->getSession();
		if (!$sess['uid'] || !$sess['login'] || $sess['login'] == 0) {
			return false;
		}
		return true;
	}

	function hasLoginVars() {
		if (request::postString('login') && request::postString('pass')) {
			return true;
		}
		return false;
	}

	function getSession() {
		if ($this->session === NULL) {
			global $_SESSION;
			$this->session = &$_SESSION;
		}
		return $this->session;
	}

	function setSessionVar($var,$value) {
		global $_SESSION;
		$_SESSION[$var] = $value;
	}
	
	function getSessionVar($var) {
		$sess = &$this->getSession();
		return $sess[$var];	
	}

	/**
	 */
	protected function removeSessionVar($var) { #{{{
		global $_SESSION;
		unset($_SESSION[$var]);
	} # }}}

	function getUser() {
		return $this->user; 
	}

	function getUid() {
		$sess = $this->getSession();
		return $sess['uid'];
	}

	/**
	 */
	private function handleLoginError($error) { #{{{
		$this->setSessionVar('loginerror',$error);
		header("Location: index.php");
		exit;
	} # }}}


	function login() {
		$result = User::getByLogin(trim(request::postStringFatal('login')));
		if (!$result) {
			$this->handleLoginError(_('Benutzername nicht gefunden'));
		}
		if ($result->getPassword() != sha1(trim(request::postStringFatal('pass')))) {
			$this->handleLoginError(_('Das Passwort ist falsch'));
		}
		if ($result->getActive() == 0) {
			$result->setActive(User::USER_ACTIVE);
			$result->save();
		}
		if ($result->getActive() == 4) {
			new LoginException("Gesperrt");
		}
		if ($result->getDeletionMark() == 2) {
			new LoginException("Löschung");
		}
		if ($result->getVacationMode() == 1) {
			$result->setVacationMode(0);
			$this->addInformation("<font color=#FF0000>Der Urlaubsmodus wurde deaktiviert</font>");
		}
		$this->setSessionVar('uid',$result->getId());
		$this->setSessionVar('login',1);
		$this->setSessionVar('logintime',time());
		$this->setSessionVar('session_strings',0);
		$this->setSessionVar('login_verified',FALSE);
		$result->save();
		$this->user = $result;
		$this->setSessionVar('userobj',$result);

		$this->truncateSessionStrings();

		if (!$result->getSaveLogin()) {
			$this->setCookies();
		}

		// Login verzeichnen
		DB()->query("INSERT INTO stu_user_iptable (user_id,ip,session,agent,start) VALUES ('".$result->getId()."','".getenv("REMOTE_ADDR")."','".session_id()."','".getenv("HTTP_USER_AGENT")."',NOW())");
		return;
	}

	function setCookies() {
		setCookie('sstr',currentUser()->getCookieString(),(time()+86400*2));
		setCookie('uid',currentUser()->getId(),(time()+86400*2));
	}

	private function destroySession() {
		$this->truncateSessionStrings();
		$this->destroyLoginCookies();
		setCookie(session_name(),'',time()-42000);
		@session_destroy();
	}

	/**
	 */
	private function destroyLoginCookies() { #{{{
		setCookie('sstr',0);
		setCookie('uid',0);
	} # }}}

	public function logout() {
		$this->destroySession();
		header("Location: index.php");
		exit;
	}

	function performCookieLogin(&$uid,&$sstr) {
		if (strlen($sstr) != 40) {
			$this->destroySession();
			return FALSE;
		}
		$result = User::getUserById($uid);
		if (!$result) {
			$this->destroySession();
			return FALSE;
		}
		if ($result->getCookieString() != $sstr) {
			$this->destroySession();
			return FALSE;
		}
		if ($result->getActive() == 0) {
			new LoginException("Aktivierung");
		}
		if ($result->getActive() == 4) {
			new LoginException("Gesperrt");
		}
		if ($result->getDeletionMark() == 2) {
			new LoginException("Löschung");
		}
		if ($result->getVacationMode() == 1) {
			$result->setVacationMode(0);
			$this->addInformation("<font color=#FF0000>Der Urlaubsmodus wurde deaktiviert</font>");
		}
		$this->setSessionVar('uid',$result->getId());
		$this->setSessionVar('login',1);
		$this->setSessionVar('logintime',time());
		$this->setSessionVar('session_strings',0);
		$result->save();
		$this->user = $result;
		$this->setSessionVar('userobj',$result);

		$this->truncateSessionStrings();
		session_start();

		// Login verzeichnen
		DB()->query("INSERT INTO stu_user_iptable (user_id,ip,session,agent,start) VALUES ('".$result->getId()."','".getenv("REMOTE_ADDR")."','".session_id()."','".getenv("HTTP_USER_AGENT")."',NOW())");

		return;
	}

	/**
	 */
	private function hasCaptchaCredentials() { #{{{
		return request::postString('B_VERIFY_LOGIN');
	} # }}}

	function chklogin() {
		if (!$this->isLoggedIn()) {
			new LoginException("Not logged in");
		}
		DB()->query("UPDATE stu_user SET lastaction='".time()."' WHERE id=".$this->getUid()." LIMIT 1");
		DB()->query("UPDATE stu_user_iptable SET end=NOW() WHERE session='".session_id()."' LIMIT 1");
		$data = DB()->query("SELECT * FROM stu_user WHERE id=".$this->getUid()." LIMIT 1",4);
		if ($data == 0) {
			$this->logout();
		}
		$this->setSessionVar('username',$data['user']);
		$this->setSessionVar('login',1);
		$this->setSessionVar('lastaction',time());

		$this->user = new UserData($data);
		$this->setSessionVar('userobj',$this->user);

		if (!$this->getSessionVar('login_verified') && !$this->hasCaptchaCredentials()) {
			$this->enforceLoginVerification();
		}
		return;
	}

	/**
	 */
	protected function enforceLoginVerification() { #{{{
		if (!USER_VERIFICATION || isAdmin(currentUser()->getId())) {
			return;
		}
		$this->setTemplateFile('html/loginverification.xhtml');
		$this->setPageTitle(_('Login bestätigen'));
		require_once('lib/recaptcha/recaptchalib.php');
		$this->getTemplate()->setVar('CAPTCHA',recaptcha_get_html(RECAPTCHA_PUBLIC_KEY));
		$this->render($this);
		exit;
	} # }}}

	/**
	 */
	protected function verifyLoginCaptcha() { #{{{
		require_once('lib/recaptcha/recaptchalib.php');
		$resp = recaptcha_check_answer (RECAPTCHA_PRIVATE_KEY,
						$_SERVER['REMOTE_ADDR'],
						$_POST['recaptcha_challenge_field'],
						$_POST['recaptcha_response_field']);

		if (!$resp->is_valid) {
			$this->enforceLoginVerification();
		}
		$this->setSessionVar('login_verified',TRUE);
	} # }}}


	private $sessionIsSafe = FALSE;

	function sessionIsSafe() {
		if ($this->sessionIsSafe === FALSE) {
			$this->sessionIsSafe = $this->checkSessionString(request::indString('sstr'));
		}
		return $this->sessionIsSafe;
	}

	function checkSessionString(&$string) {
		$result = DB()->query("DELETE FROM stu_session_strings WHERE user_id=".$this->getUid()." AND sess_string='".dbsafe($string)."'",6);
		if ($result == 0) {
			return FALSE;
		}
		return TRUE;
	}

	function truncateSessionStrings() {
		DB()->query("DELETE FROM stu_session_strings WHERE UNIX_TIMESTAMP(date)<".(time()-3600)." OR user_id=".currentUser()->getId());
	}

	function generateSessionString() {
		$this->setSessionVar('session_strings',$this->getSessionVar('session_strings')+1);
		return substr(md5(getUniqId()*$this->getSessionVar('session_strings')),rand(1,4),rand(15,20));
	}

	function getSessionString() {
		$string = $this->generateSessionString();
		$this->registerSessionString($string);
		return $string;
	}

	function registerSessionString(&$string) {
		DB()->query("INSERT INTO stu_session_strings (sess_string,user_id,date) VALUES ('".$string."','".currentUser()->getId()."',NOW())");
	}

	protected function storeSessionData($key,$value) {
		$data = currentUser()->getSessionDataUnserialized();
		if (!array_key_exists($key,$data)) {
			$data[$key] = array();
		}
		if (!array_key_exists($value,$data[$key])) {
			$data[$key][$value] = 1;
			currentUser()->setSessionData(serialize($data));
			currentUser()->save();
		}
	}

	protected function deleteSessionData($key,$value) {
		$data = currentUser()->getSessionDataUnserialized();
		if (!array_key_exists($key,$data)) {
			return;
		}
		if (!array_key_exists($value,$data[$key])) {
			return;
		}
		unset($data[$key][$value]);
		currentUser()->setSessionData(serialize($data));
		currentUser()->save();
	}

	protected function hasSessionValue($key,$value) {
		$data = currentUser()->getSessionDataUnserialized();
		if (!array_key_exists($key,$data)) {
			return FALSE;
		}
		if (!array_key_exists($value,$data[$key])) {
			return FALSE;
		}
		return TRUE;
	}
}

function &currentUser() {
	static $currentUser = NULL;
	if ($currentUser === NULL) {
		global $_SESSION;
		$currentUser = $_SESSION['userobj'];
	}
	return $currentUser;
}
