<?php

namespace Stu\Lib;

use LoginException;
use request;
use User;
use UserData;

final class Session implements SessionInterface
{

    private $user;

    private $sessionIsSafe;

    private $db;

    public function __construct(
        DbInterface $db
    )
    {
        $this->db = $db;
    }

    public function createSession(bool $session_check = true): void
    {
        if ($this->hasLoginVars()) {
            $this->destroyLoginCookies();
            $this->login();
        }
        if (!$this->isLoggedIn() && !$this->hasLoginVars() && $session_check) {
            throw new LoginException("Session abgelaufen");
        }
        if (!$this->hasLoginVars() && $session_check && (!$_SESSION['uid'] || !$_SESSION['login'])) {
            $this->logout();
            return;
        }
        if ($this->isLoggedIn() && $session_check) {
            $this->chklogin();
        }
    }

    /**
     * @api
     */
    public function checkLoginCookie()
    {
        $sstr = $_COOKIE['sstr'];
        $uid = intval($_SESSION['uid']);
        if ($uid > 0) {
            return $this->performCookieLogin($uid, $sstr);
        }
        return false;
    }

    private function isLoggedIn()
    {
        if (!$_SESSION['uid'] || !$_SESSION['login'] || $_SESSION['login'] == 0) {
            return false;
        }
        return true;
    }

    private function hasLoginVars()
    {
        if (request::postString('login') && request::postString('pass')) {
            return true;
        }
        return false;
    }

    /**
     * @api
     */
    public function setSessionVar($var, $value)
    {
        global $_SESSION;
        $_SESSION[$var] = $value;
    }

    /**
     * @api
     */
    public function getSessionVar($var)
    {
        return $_SESSION[$var];
    }

    /**
     * @api
     */
    public function removeSessionVar($var)
    {
        unset($_SESSION[$var]);
    }

    /**
     * @api
     */
    public function getUser()
    {
        return $this->user;
    }

    private function getUid()
    {
        return $_SESSION['uid'];
    }

    /**
     */
    private function handleLoginError($error)
    {
        $this->setSessionVar('loginerror', $error);
        header("Location: index.php");
        exit;
    }

    private function login()
    {
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
            throw new LoginException("Gesperrt");
        }
        if ($result->getDeletionMark() == 2) {
            throw new LoginException("Löschung");
        }
        if ($result->getVacationMode() == 1) {
            $result->setVacationMode(0);
        }
        $this->setSessionVar('uid', $result->getId());
        $this->setSessionVar('login', 1);
        $this->setSessionVar('logintime', time());
        $this->setSessionVar('session_strings', 0);
        $this->setSessionVar('login_verified', false);
        $result->save();
        $this->user = $result;

        $this->truncateSessionStrings();

        if (!$result->getSaveLogin()) {
            setcookie('sstr', currentUser()->getCookieString(), (time() + 86400 * 2));
        }

        // Login verzeichnen
        $this->db->query("INSERT INTO stu_user_iptable (user_id,ip,session,agent,start) VALUES ('" . $result->getId() . "','" . getenv("REMOTE_ADDR") . "','" . session_id() . "','" . dbSafe(getenv("HTTP_USER_AGENT")) . "',NOW())");
    }

    private function destroySession()
    {
        $this->truncateSessionStrings();
        $this->destroyLoginCookies();
        setCookie(session_name(), '', time() - 42000);
        @session_destroy();
    }

    /**
     */
    private function destroyLoginCookies()
    {
        setCookie('sstr', 0);
    }

    public function logout()
    {
        $this->destroySession();
        header("Location: index.php");
        exit;
    }

    private function performCookieLogin(&$uid, &$sstr)
    {
        if (strlen($sstr) != 40) {
            $this->destroySession();
            return false;
        }
        $result = User::getUserById($uid);
        if (!$result) {
            $this->destroySession();
            return false;
        }
        if ($result->getCookieString() != $sstr) {
            $this->destroySession();
            return false;
        }
        if ($result->getActive() == 0) {
            throw new LoginException("Aktivierung");
        }
        if ($result->getActive() == 4) {
            throw new LoginException("Gesperrt");
        }
        if ($result->getDeletionMark() == 2) {
            throw new LoginException("Löschung");
        }
        if ($result->getVacationMode() == 1) {
            $result->setVacationMode(0);
        }
        $this->setSessionVar('uid', $result->getId());
        $this->setSessionVar('login', 1);
        $this->setSessionVar('logintime', time());
        $this->setSessionVar('session_strings', 0);
        $result->save();
        $this->user = $result;

        $this->truncateSessionStrings();
        session_start();

        // Login verzeichnen
        $this->db->query("INSERT INTO stu_user_iptable (user_id,ip,session,agent,start) VALUES ('" . $result->getId() . "','" . getenv("REMOTE_ADDR") . "','" . session_id() . "','" . dbsafe(getenv("HTTP_USER_AGENT")) . "',NOW())");
    }

    private function chklogin()
    {
        if (!$this->isLoggedIn()) {
            new LoginException("Not logged in");
        }
        $this->db->query("UPDATE stu_user SET lastaction='" . time() . "' WHERE id=" . $this->getUid() . " LIMIT 1");
        $this->db->query("UPDATE stu_user_iptable SET end=NOW() WHERE session='" . session_id() . "' LIMIT 1");
        $data = $this->db->query("SELECT * FROM stu_user WHERE id=" . $this->getUid() . " LIMIT 1", 4);
        if ($data == 0) {
            $this->logout();
        }
        $this->setSessionVar('username', $data['user']);
        $this->setSessionVar('login', 1);
        $this->setSessionVar('lastaction', time());

        $this->user = new UserData($data);
    }

    /**
     * @api
     */
    public function sessionIsSafe()
    {
        if ($this->sessionIsSafe === null) {
            $this->sessionIsSafe = $this->checkSessionString(request::indString('sstr'));
        }
        return $this->sessionIsSafe;
    }

    private function checkSessionString(&$string)
    {
        $result = $this->db->query("DELETE FROM stu_session_strings WHERE user_id=" . $this->getUid() . " AND sess_string='" . dbsafe($string) . "'",
            6);
        if ($result == 0) {
            return false;
        }
        return true;
    }

    private function truncateSessionStrings()
    {
        $this->db->query("DELETE FROM stu_session_strings WHERE UNIX_TIMESTAMP(date)<" . (time() - 3600) . " OR user_id=" . currentUser()->getId());
    }

    private function generateSessionString()
    {
        $this->setSessionVar('session_strings', $this->getSessionVar('session_strings') + 1);
        return substr(md5(getUniqId() * $this->getSessionVar('session_strings')), rand(1, 4), rand(15, 20));
    }

    /**
     * @api
     */
    public function getSessionString()
    {
        $string = $this->generateSessionString();
        $this->db->query("INSERT INTO stu_session_strings (sess_string,user_id,date) VALUES ('" . $string . "','" . currentUser()->getId() . "',NOW())");
        return $string;
    }

    /**
     * @api
     */
    public function storeSessionData($key, $value)
    {
        $data = currentUser()->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            $data[$key] = array();
        }
        if (!array_key_exists($value, $data[$key])) {
            $data[$key][$value] = 1;
            currentUser()->setSessionData(serialize($data));
            currentUser()->save();
        }
    }

    /**
     * @api
     */
    public function deleteSessionData($key, $value)
    {
        $data = currentUser()->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return;
        }
        if (!array_key_exists($value, $data[$key])) {
            return;
        }
        unset($data[$key][$value]);
        currentUser()->setSessionData(serialize($data));
        currentUser()->save();
    }

    /**
     * @api
     */
    public function hasSessionValue($key, $value)
    {
        $data = currentUser()->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return false;
        }
        if (!array_key_exists($value, $data[$key])) {
            return false;
        }
        return true;
    }
}
