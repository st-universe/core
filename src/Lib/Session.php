<?php

namespace Stu\Lib;

use DateTime;
use Override;
use RuntimeException;
use Stu\Component\Game\TimeConstants;
use Stu\Exception\SessionInvalidException;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLockInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Session implements SessionInterface
{
    private LoggerUtilInterface $loggerUtil;

    private ?UserInterface $user = null;

    public function __construct(
        private UserIpTableRepositoryInterface $userIpTableRepository,
        private SessionStringRepositoryInterface $sessionStringRepository,
        private UserRepositoryInterface $userRepository,
        private StuHashInterface $stuHash,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
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
    #[Override]
    public function checkLoginCookie(): void
    {
        $sstr = $_COOKIE['sstr'] ?? '';
        $uid = (int) ($_SESSION['uid'] ?? 0);
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
    #[Override]
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    #[Override]
    public function login(string $login, string $password): bool
    {
        $this->destroyLoginCookies();

        $user = $this->loadUser($login);

        $this->checkPassword($user, $password);
        $this->checkUser($user);
        $this->updateUser($user);
        $this->setUser($user);

        $this->sessionStringRepository->truncate($user);

        if (!$user->isSaveLogin()) {
            $cookieString = $this->buildCookieString($user);
            $this->loggerUtil->log(sprintf('noSaveLogin, set cookieString: %s', $cookieString));
            setcookie('sstr', $cookieString, ['expires' => time() + TimeConstants::TWO_DAYS_IN_SECONDS]);
        }

        // register login
        $this->addIpTableEntry($user);

        return true;
    }

    private function loadUser(string $login): UserInterface
    {
        $user = $this->userRepository->getByLogin(mb_strtolower($login));
        if ($user === null) {
            if (is_numeric($login)) {
                $user = $this->userRepository->find((int)$login);
            }

            if ($user === null) {
                throw new LoginException(_('Login oder Passwort inkorrekt'));
            }
        }

        return $user;
    }

    private function checkPassword(UserInterface $user, string $password): void
    {
        $password_hash = $user->getPassword();

        if (!password_verify($password, $password_hash)) {
            throw new LoginException(_('Login oder Passwort inkorrekt'));
        }

        if (password_needs_rehash($password_hash, PASSWORD_DEFAULT)) {
            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

            $this->userRepository->save($user);
        }
    }

    private function checkUser(UserInterface $user): void
    {
        if ($user->isLocked()) {
            /** @var UserLockInterface $userLock */
            $userLock = $user->getUserLock();

            throw new UserLockedException(
                _('Dein Spieleraccount wurde gesperrt'),
                sprintf(_('Dein Spieleraccount ist noch für %d Ticks gesperrt. Begründung: %s'), $userLock->getRemainingTicks(), $userLock->getReason())
            );
        }
        if ($user->getDeletionMark() === UserEnum::DELETION_CONFIRMED) {
            throw new LoginException(_('Dein Spieleraccount ist zur Löschung vorgesehen'));
        }
    }

    private function updateUser(UserInterface $user): void
    {
        if ($user->getState() === UserEnum::USER_STATE_NEW) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);

            $this->userRepository->save($user);
        }

        if ($user->isVacationMode()) {
            $user->setVacationMode(false);
        }

        $this->userRepository->save($user);
    }

    private function setUser(UserInterface $user): void
    {
        $_SESSION['uid'] = $user->getId();
        $_SESSION['login'] = 1;

        $this->user = $user;
    }

    private function addIpTableEntry(UserInterface $user): void
    {
        $ipTableEntry = $this->userIpTableRepository->prototype();
        $ipTableEntry->setUser($user);
        $ipTableEntry->setIp((string) getenv('REMOTE_ADDR'));
        $ipTableEntry->setSessionId((string) session_id());
        $ipTableEntry->setUserAgent((string) getenv('HTTP_USER_AGENT'));
        $ipTableEntry->setStartDate(new DateTime());

        $this->userIpTableRepository->save($ipTableEntry);
    }

    private function buildCookieString(UserInterface $user): string
    {
        return $this->stuHash->hash(($user->getId() . $user->getEMail() . $user->getCreationDate()));
    }

    private function destroySession(?UserInterface $user = null): void
    {
        if ($this->user !== null || $user !== null) {
            $userToTruncate = $user ?? $this->user;
            $this->sessionStringRepository->truncate($userToTruncate);
        }

        if ($user === null) {
            $this->destroyLoginCookies();
            setcookie(session_name(), '', ['expires' => time() - 42000]);
            if (@session_destroy() === false) {
                throw new RuntimeException('The session could not be destroyed');
            }

            $this->user = null;
        }
    }

    private function destroyLoginCookies(): void
    {
        setcookie('sstr', 0);
    }

    #[Override]
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
        $user = $this->userRepository->find($uid);
        if ($user === null) {
            $this->destroySession();
            return;
        }
        if ($this->buildCookieString($user) !== $sstr) {
            $this->destroySession();
            return;
        }
        if ($user->getState() == UserEnum::USER_STATE_NEW) {
            throw new SessionInvalidException("Aktivierung");
        }
        if ($user->isLocked()) {
            throw new SessionInvalidException("Gesperrt");
        }
        if ($user->getDeletionMark() === UserEnum::DELETION_CONFIRMED) {
            throw new SessionInvalidException("Löschung");
        }
        if ($user->isVacationMode() === true) {
            $user->setVacationMode(false);
        }
        $this->userRepository->save($user);

        $this->setUser($user);

        $this->sessionStringRepository->truncate($user);

        //start session if not already active
        if (session_id() == '') {
            session_start();
        }

        // register login
        $this->addIpTableEntry($user);
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
            $ipTableEntry->setEndDate(new DateTime());

            $this->userIpTableRepository->save($ipTableEntry);
        }

        $_SESSION['login'] = 1;

        $this->user = $user;
    }

    /**
     * @api
     */
    #[Override]
    public function storeSessionData($key, $value, bool $isSingleValue = false): void
    {
        $stored = false;

        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            if ($isSingleValue) {
                $data[$key] = $value;
                $stored = true;
            } else {
                $data[$key] = [];
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
    #[Override]
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
    #[Override]
    public function hasSessionValue($key, $value): bool
    {
        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return false;
        }
        return array_key_exists($value, $data[$key]);
    }

    /**
     * @api
     */
    #[Override]
    public function getSessionValue($key)
    {
        $data = $this->user->getSessionDataUnserialized();
        if (!array_key_exists($key, $data)) {
            return false;
        }
        return $data[$key];
    }
}
