<?php

namespace Stu\Lib;

use DateTimeImmutable;
use Stu\Exception\SessionInvalidException;
use Stu\Component\Player\Validation\LoginValidationInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Session implements SessionInterface
{

    private $userIpTableRepository;

    private $sessionStringRepository;

    private $userRepository;

    private $loginValidation;

    /**
     * @var UserInterface|null
     */
    private $user;

    public function __construct(
        UserIpTableRepositoryInterface $userIpTableRepository,
        SessionStringRepositoryInterface $sessionStringRepository,
        UserRepositoryInterface $userRepository,
        LoginValidationInterface $loginValidation
    ) {
        $this->userIpTableRepository = $userIpTableRepository;
        $this->sessionStringRepository = $sessionStringRepository;
        $this->userRepository = $userRepository;
        $this->loginValidation = $loginValidation;
    }

    public function createSession(bool $session_check = true): void
    {
        if (!$this->isLoggedIn() && $session_check) {
            throw new SessionInvalidException('Session abgelaufen');
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
            && array_key_exists('login', $_SESSION)
            && $_SESSION['login'] == 1;
    }

    /**
     * @api
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function login(string $login, string $password): void
    {
        $this->destroyLoginCookies();

        $result = $this->userRepository->getByLogin(mb_strtolower($login));
        if ($result === null) {
            if (is_numeric($login)) {
                $result = $this->userRepository->find((int)$login);
            }

            if ($result === null) {
                throw new LoginException(_('Login oder Passwort inkorrekt'));
            }
        }

        $password_hash = $result->getPassword();

        $password_info = password_get_info($password_hash);

        if ($password_info['algo'] === 0) {
            // @todo remove old password handling. This is just temporary
            if ($password_hash !== sha1($password)) {
                throw new LoginException(_('Login oder Passwort inkorrekt'));
            }
            $result->setPassword(password_hash($password, PASSWORD_DEFAULT));

            $this->userRepository->save($result);
        } else {
            if (!password_verify($password, $password_hash)) {
                throw new LoginException(_('Login oder Passwort inkorrekt'));
            }

            if (password_needs_rehash($password_hash, PASSWORD_DEFAULT)) {
                $result->setPassword(password_hash($password, PASSWORD_DEFAULT));

                $this->userRepository->save($result);
            }
        }

        if ($result->getActive() === UserEnum::USER_STATE_NEW) {
            $result->setActive(UserEnum::USER_STATE_UNCOLONIZED);

            $this->userRepository->save($result);
        }
        if ($result->isLocked()) {
            $userLock = $result->getUserLock();
            throw new LoginException(
                _('Dein Spieleraccount wurde gesperrt'),
                sprintf(_('Dein Spieleraccount ist noch für %d Ticks gesperrt. Begründung: %s'), $userLock->getRemainingTicks(), $userLock->getReason())
            );
        }
        if ($result->getDeletionMark() === UserEnum::DELETION_CONFIRMED) {
            throw new LoginException(_('Dein Spieleraccount wurde zur Löschung vorgesehen'));
        }
        if ($this->loginValidation->validate($result) === false) {
            throw new LoginException(_('Login fehlgeschlagen'));
        }

        if ($result->isVacationMode()) {
            $result->setVacationMode(false);
        }

        $this->userRepository->save($result);

        $_SESSION['uid'] = $result->getId();
        $_SESSION['login'] = 1;

        $this->user = $result;

        $this->sessionStringRepository->truncate($result);

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

    private function buildCookieString(UserInterface $user): string
    {
        return sha1($user->getId() . $user->getEMail() . $user->getCreationDate());
    }

    private function destroySession(?UserInterface $user = null): void
    {
        if ($this->user !== null || $user !== null) {
            $userToTruncate = $user ?? $this->user;
            $this->sessionStringRepository->truncate($userToTruncate);
        }

        if ($user === null) {
            $this->destroyLoginCookies();
            setCookie(session_name(), '', time() - 42000);
            @session_destroy();

            $this->user = null;
        }
    }

    private function destroyLoginCookies(): void
    {
        setCookie('sstr', 0);
    }

    public function logout(?UserInterface $user = null): void
    {
        $this->destroySession($user);
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
        if ($result->getActive() == UserEnum::USER_STATE_NEW) {
            throw new SessionInvalidException("Aktivierung");
        }
        if ($result->isLocked()) {
            throw new SessionInvalidException("Gesperrt");
        }
        if ($result->getDeletionMark() === UserEnum::DELETION_CONFIRMED) {
            throw new SessionInvalidException("Löschung");
        }
        if ($result->isVacationMode() === true) {
            $result->setVacationMode(false);
        }
        $this->userRepository->save($result);

        $_SESSION['uid'] = $result->getId();
        $_SESSION['login'] = 1;

        $this->user = $result;

        $this->sessionStringRepository->truncate($result);
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
            throw new SessionInvalidException("Not logged in");
        }

        $userId = (int) $_SESSION['uid'];

        $user = $this->userRepository->find($userId);

        if ($user === null) {
            $this->logout();
            return;
        }
        $user->setLastaction(time());

        $this->userRepository->save($user);


        $ipTableEntry = $this->userIpTableRepository->findBySessionId(session_id());
        if ($ipTableEntry !== null) {
            $ipTableEntry->setEndDate(new DateTimeImmutable());

            $this->userIpTableRepository->save($ipTableEntry);
        }

        if ($user === null) {
            $this->logout();
        }
        $_SESSION['login'] = 1;

        $this->user = $user;
    }

    /**
     * @api
     */
    public function storeSessionData($key, $value, bool $isSingleValue = false): void
    {
        $stored = false;

        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            if ($isSingleValue) {
                $data[$key] = $value;
                $stored = true;
            } else {
                $data[$key] = array();
            }
        }
        if (!$isSingleValue && !array_key_exists($value, $data[$key])) {
            $data[$key][$value] = 1;
            $stored = true;
        }

        if ($stored) {
            $this->user->setSessionData(serialize($data));
            $this->userRepository->save($this->user);
        }
    }

    /**
     * @api
     */
    public function deleteSessionData($key, $value = null): void
    {
        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return;
        }
        if ($value === null) {
            unset($data[$key]);
        } else {
            if (!array_key_exists($value, $data[$key])) {
                return;
            }
            unset($data[$key][$value]);
        }
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

    /**
     * @api
     */
    public function getSessionValue($key)
    {
        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return false;
        }
        return $data[$key];
    }
}
