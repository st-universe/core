<?php

namespace Stu\Lib\Session;

use RuntimeException;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

final class SessionDestruction implements SessionDestructionInterface
{
    public function __construct(
        private SessionStringRepositoryInterface $sessionStringRepository
    ) {}

    public function destroySession(SessionInterface $session, ?User $user = null): void
    {
        $userToTruncate = $user ?? $session->getUser();
        if ($userToTruncate !== null) {
            $this->sessionStringRepository->truncate($userToTruncate);
        }

        if ($user === null) {
            $this->destroyLoginCookies();
            $sessionName = session_name();
            if ($sessionName) {
                setcookie($sessionName, '', time() - 42000, "", "", true);
            }
            if (@session_destroy() === false) {
                throw new RuntimeException('The session could not be destroyed');
            }

            $session->setUser(null);
        }
    }

    private function destroyLoginCookies(): void
    {
        setcookie('sstr', "", 0, "", "", true);
    }
}
