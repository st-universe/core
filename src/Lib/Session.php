<?php

namespace Stu\Lib;

use DateTimeImmutable;
use LoginException;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Session implements SessionInterface
{

    private $db;

    private $userIpTableRepository;

    private $sessionStringRepository;

    private $userRepository;

    /**
     * @var UserInterface|null
     */
    private $user;

    public function __construct(
        DbInterface $db,
        UserIpTableRepositoryInterface $userIpTableRepository,
        SessionStringRepositoryInterface $sessionStringRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->db = $db;
        $this->userIpTableRepository = $userIpTableRepository;
        $this->sessionStringRepository = $sessionStringRepository;
        $this->userRepository = $userRepository;
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
        $sstr = $_COOKIE['sstr'] ?? '';
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
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function login(string $userName, string $password): void
    {
        $this->destroyLoginCookies();

        $result = $this->userRepository->getByLogin($userName);
        if ($result === null) {
            throw new \Stu\Lib\LoginException(_('Benutzername nicht gefunden'));
        }
        if ($result->getPassword() != sha1($password)) {
            throw new \Stu\Lib\LoginException(_('Das Passwort ist falsch'));
        }
        if ($result->getActive() == 0) {
            $result->setActive(PlayerEnum::USER_ACTIVE);

            $this->userRepository->save($result);
        }
        if ($result->getActive() == 4) {
            throw new \Stu\Lib\LoginException(_('Dein Spieleraccount wurde gesperrt'));
        }
        if ($result->getDeletionMark() == 2) {
            throw new \Stu\Lib\LoginException(_('Dein Spieleraccount wurde zur Löschung vorgesehen'));
        }
        if ($result->isVacationMode()) {
            $result->setVacationMode(false);
        }

        $this->userRepository->save($result);

        $_SESSION['uid'] = $result->getId();
        $_SESSION['login'] = 1;

        $this->user = $result;

        $this->sessionStringRepository->truncate($result->getId());

        if (!$result->isSaveLogin()) {
            setcookie('sstr', $this->buildCookieString($result), (time() + 86400 * 2));
        }

        // Login verzeichnen
        $ipTableEntry = $this->userIpTableRepository->prototype();
        $ipTableEntry->setUser($result);
        $ipTableEntry->setIp(getenv('REMOTE_ADDR'));
        $ipTableEntry->setSessionId(session_id());
        $ipTableEntry->setUserAgent(getenv('HTTP_USER_AGENT'));
        $ipTableEntry->setStartDate(new DateTimeImmutable());

        $this->userIpTableRepository->save($ipTableEntry);
    }

    private function buildCookieString(UserInterface $user): string {
        return sha1($user->getId().$user->getEMail().$user->getCreationDate());
    }

    private function destroySession(): void
    {
        if ($this->user !== null) {
            $this->sessionStringRepository->truncate($this->user->getId());
        }
        $this->destroyLoginCookies();
        setCookie(session_name(), '', time() - 42000);
        @session_destroy();

        $this->user = null;
    }

    private function destroyLoginCookies(): void
    {
        setCookie('sstr', 0);
    }

    public function logout(): void
    {
        $this->destroySession();
    }

    private function performCookieLogin(int $uid, string $sstr): void
    {
        if (strlen($sstr) != 40) {
            $this->destroySession();
            return;
        }
        $result = $this->userRepository->find($uid);
        if ($result === null) {
            $this->destroySession();
            return;
        }
        if ($this->buildCookieString($result) != $sstr) {
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
        if ($result->isVacationMode() === true) {
            $result->setVacationMode(false);
        }
        $this->userRepository->save($result);

        $_SESSION['uid'] = $result->getId();
        $_SESSION['login'] = 1;

        $this->user = $result;

        $this->sessionStringRepository->truncate($result->getId());
        session_start();

        // Login verzeichnen
        $ipTableEntry = $this->userIpTableRepository->prototype();
        $ipTableEntry->setUser($result);
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

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            $this->logout();
        }
        $_SESSION['login'] = 1;

        $this->user = $user;
    }

    /**
     * @api
     */
    public function storeSessionData($key, $value): void
    {
        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            $data[$key] = array();
        }
        if (!array_key_exists($value, $data[$key])) {
            $data[$key][$value] = 1;
            $this->user->setSessionData(serialize($data));
            $this->userRepository->save($this->user);
        }
    }

    /**
     * @api
     */
    public function deleteSessionData($key, $value): void
    {
        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return;
        }
        if (!array_key_exists($value, $data[$key])) {
            return;
        }
        unset($data[$key][$value]);
        $this->user->setSessionData(serialize($data));
        $this->userRepository->save($this->user);
    }

    /**
     * @api
     */
    public function hasSessionValue($key, $value): bool
    {
        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return false;
        }
        if (!array_key_exists($value, $data[$key])) {
            return false;
        }
        return true;
    }
}
