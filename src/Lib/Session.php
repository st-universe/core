<?php

namespace Stu\Lib;

use DateTimeImmutable;
use LoginException;
use request;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use User;
use UserData;
use Zend\Validator\Date;

final class Session implements SessionInterface
{

    private $user;

    private $db;

    private $userIpTableRepository;

    private $sessionStringRepository;

    public function __construct(
        DbInterface $db,
        UserIpTableRepositoryInterface $userIpTableRepository,
        SessionStringRepositoryInterface $sessionStringRepository
    ) {
        $this->db = $db;
        $this->userIpTableRepository = $userIpTableRepository;
        $this->sessionStringRepository = $sessionStringRepository;
    }

    public function createSession(bool $session_check = true): void
    {
        if (!$this->isLoggedIn() && $session_check) {
            throw new LoginException('Session abgelaufen');
        }
        if ($session_check && (!$_SESSION['uid'] || !$_SESSION['login'])) {
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
    public function checkLoginCookie(): void
    {
        $sstr = $_COOKIE['sstr'];
        $uid = intval($_SESSION['uid']);
        if ($uid > 0) {
            $this->performCookieLogin($uid, $sstr);
        }
    }

    private function isLoggedIn(): bool
    {
        return array_key_exists('uid', $_SESSION)
            && array_key_exists('login',$_SESSION)
            && $_SESSION['login'] == 1;
    }

    /**
     * @api
     */
    public function getUser(): ?UserData
    {
        return $this->user;
    }

    public function login(string $userName, string $password): void
    {
        $this->destroyLoginCookies();

        $result = User::getByLogin($userName);
        if (!$result) {
            throw new \Stu\Lib\LoginException(_('Benutzername nicht gefunden'));
        }
        if ($result->getPassword() != sha1($password)) {
            throw new \Stu\Lib\LoginException(_('Das Passwort ist falsch'));
        }
        if ($result->getActive() == 0) {
            $result->setActive(User::USER_ACTIVE);
            $result->save();
        }
        if ($result->getActive() == 4) {
            throw new \Stu\Lib\LoginException(_('Dein Spieleraccount wurde gesperrt'));
        }
        if ($result->getDeletionMark() == 2) {
            throw new \Stu\Lib\LoginException(_('Dein Spieleraccount wurde zur Löschung vorgesehen'));
        }
        if ($result->getVacationMode() == 1) {
            $result->setVacationMode(0);
        }
        $result->save();

        $_SESSION['uid'] = $result->getId();
        $_SESSION['login'] = 1;

        $this->user = $result;

        $this->sessionStringRepository->truncate($result->getId());

        if (!$result->getSaveLogin()) {
            setcookie('sstr', $this->user->getCookieString(), (time() + 86400 * 2));
        }

        // Login verzeichnen
        $ipTableEntry = $this->userIpTableRepository->prototype();
        $ipTableEntry->setUserId((int) $result->getId());
        $ipTableEntry->setIp(getenv('REMOTE_ADDR'));
        $ipTableEntry->setSessionId(session_id());
        $ipTableEntry->setUserAgent(getenv('HTTP_USER_AGENT'));
        $ipTableEntry->setStartDate(new DateTimeImmutable());

        $this->userIpTableRepository->save($ipTableEntry);
    }

    private function destroySession(): void
    {
        $this->sessionStringRepository->truncate($this->user->getId());
        $this->destroyLoginCookies();
        setCookie(session_name(), '', time() - 42000);
        @session_destroy();
    }

    private function destroyLoginCookies(): void
    {
        setCookie('sstr', 0);
    }

    public function logout(): void
    {
        $this->destroySession();
        header('Location: /');
    }

    private function performCookieLogin(int $uid, string $sstr): void
    {
        if (strlen($sstr) != 40) {
            $this->destroySession();
            return;
        }
        $result = User::getUserById($uid);
        if (!$result) {
            $this->destroySession();
            return;
        }
        if ($result->getCookieString() != $sstr) {
            $this->destroySession();
            return;
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
        $result->save();

        $_SESSION['uid'] = $result->getId();
        $_SESSION['login'] = 1;

        $this->user = $result;

        $this->sessionStringRepository->truncate($result->getId());
        session_start();

        // Login verzeichnen
        $ipTableEntry = $this->userIpTableRepository->prototype();
        $ipTableEntry->setUserId((int) $result->getId());
        $ipTableEntry->setIp(getenv('REMOTE_ADDR'));
        $ipTableEntry->setSessionId(session_id());
        $ipTableEntry->setUserAgent(getenv('HTTP_USER_AGENT'));
        $ipTableEntry->setStartDate(new DateTimeImmutable());

        $this->userIpTableRepository->save($ipTableEntry);
    }

    private function chklogin(): void
    {
        if (!$this->isLoggedIn()) {
            throw new LoginException("Not logged in");
        }

        $userId = (int) $_SESSION['uid'];

        $this->db->query("UPDATE stu_user SET lastaction='" . time() . "' WHERE id=" . $userId . " LIMIT 1");

        $ipTableEntry = $this->userIpTableRepository->findBySessionId(session_id());
        if ($ipTableEntry !== null) {
            $ipTableEntry->setEndDate(new DateTimeImmutable());

            $this->userIpTableRepository->save($ipTableEntry);
        }

        $data = $this->db->query("SELECT * FROM stu_user WHERE id=" . $userId . " LIMIT 1", 4);
        if ($data == 0) {
            $this->logout();
        }
        $_SESSION['login'] = 1;

        $this->user = new UserData($data);
    }

    /**
     * @api
     */
    public function storeSessionData($key, $value): void
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
    public function deleteSessionData($key, $value): void
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
    public function hasSessionValue($key, $value): bool
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
