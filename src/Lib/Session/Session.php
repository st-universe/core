<?php

namespace Stu\Lib\Session;

use DateTime;
use Override;
use Stu\Exception\SessionInvalidException;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Session implements SessionInterface
{
    private ?User $user = null;

    public function __construct(
        private readonly UserIpTableRepositoryInterface $userIpTableRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly SessionDestructionInterface $sessionDestruction
    ) {}

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
    public function getUser(): ?User
    {
        return $this->user;
    }

    #[Override]
    public function setUser(?User $user): SessionInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function logout(?User $user = null): void
    {
        $this->sessionDestruction->destroySession($this, $user);
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

        $sessionId = session_id();
        if (!$sessionId) {
            throw new SessionInvalidException("Session Id not set");
        }

        $ipTableEntry = $this->userIpTableRepository->findBySessionId($sessionId);
        if ($ipTableEntry !== null) {
            $ipTableEntry->setEndDate(new DateTime());

            $this->userIpTableRepository->save($ipTableEntry);
        }

        $_SESSION['login'] = 1;

        $this->user = $user;
    }
}
