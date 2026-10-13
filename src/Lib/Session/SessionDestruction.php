<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use RuntimeException;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

final class SessionDestruction implements SessionDestructionInterface
{
    public function __construct(
        private SessionStringRepositoryInterface $sessionStringRepository,
        private readonly AdminImpersonationInterface $impersonation
    ) {}

    #[\Override]
    public function destroySession(SessionInterface $session, ?User $user = null): void
    {
        $userToTruncate = $user ?? ($this->impersonation->isActive() ? null : $session->getUser());
        if ($userToTruncate !== null) {
            $this->sessionStringRepository->truncate($userToTruncate);
        }

        if ($user === null) {
            $_SESSION = [];
            $this->destroyLoginCookies();
            $sessionName = session_name();
            if ($sessionName) {
                setcookie($sessionName, '', time() - 42000, '', '', true, true);
            }
            if (@session_destroy() === false) {
                throw new RuntimeException('The session could not be destroyed');
            }

            $session->setUser(null);
        }
    }

    private function destroyLoginCookies(): void
    {
        setcookie('sstr', '', 0, '', '', true, true);
    }
}
