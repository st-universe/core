<?php

namespace Stu\Lib\Session;

use DateTime;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\LoginException;
use Stu\Lib\UserLockedException;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLockInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SessionLogin implements SessionLoginInterface
{
    public function __construct(
        private readonly UserIpTableRepositoryInterface $userIpTableRepository,
        private readonly SessionStringRepositoryInterface $sessionStringRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly SessionInterface $session,
        private readonly SessionDestructionInterface $sessionDestruction,
        private readonly StuHashInterface $stuHash
    ) {}

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

        if (!$this->userSettingsProvider->isSaveLogin($user)) {
            $cookieString = $this->buildCookieString($user);
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

    private function setUser(UserInterface $user): void
    {
        $_SESSION['uid'] = $user->getId();
        $_SESSION['login'] = 1;

        $this->session->setUser($user);
    }

    private function checkPassword(UserInterface $user, string $password): void
    {
        $registration = $user->getRegistration();
        $passwordHash = $registration->getPassword();

        if (!password_verify($password, $passwordHash)) {
            throw new LoginException(_('Login oder Passwort inkorrekt'));
        }

        if (password_needs_rehash($passwordHash, PASSWORD_DEFAULT)) {
            $registration->setPassword(password_hash($password, PASSWORD_DEFAULT));

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
        if ($user->getRegistration()->getDeletionMark() === UserEnum::DELETION_CONFIRMED) {
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
        return $this->stuHash->hash(($user->getId() . $user->getRegistration()->getEMail() . $user->getRegistration()->getCreationDate()));
    }

    private function destroyLoginCookies(): void
    {
        setcookie('sstr');
    }

    private function performCookieLogin(int $uid, string $sstr): void
    {
        if (strlen($sstr) != 40) {
            $this->sessionDestruction->destroySession($this->session);
            return;
        }
        $user = $this->userRepository->find($uid);
        if ($user === null) {
            $this->sessionDestruction->destroySession($this->session);
            return;
        }
        if ($this->buildCookieString($user) !== $sstr) {
            $this->sessionDestruction->destroySession($this->session);
            return;
        }
        if ($user->getState() == UserEnum::USER_STATE_NEW) {
            throw new SessionInvalidException("Aktivierung");
        }
        if ($user->isLocked()) {
            throw new SessionInvalidException("Gesperrt");
        }
        if ($user->getRegistration()->getDeletionMark() === UserEnum::DELETION_CONFIRMED) {
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
}
