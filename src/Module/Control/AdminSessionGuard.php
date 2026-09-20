<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Config\SessionStarterInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;

final class AdminSessionGuard implements AdminSessionGuardInterface
{
    private const string REDIRECT_TO_DOMAIN_ROOT = 'Location: /';

    public function __construct(
        private readonly SessionStarterInterface $sessionStarter,
        private readonly SessionInterface $session,
        private readonly StuConfigInterface $stuConfig
    ) {}

    #[\Override]
    public function check(): void
    {
        $this->sessionStarter->start();
        $this->session->createSession();

        $user = $this->session->getUser();
        $adminIds = $this->stuConfig->getGameSettings()->getAdminIds();
        if ($user === null || !in_array($user->getId(), $adminIds, true)) {
            header(self::REDIRECT_TO_DOMAIN_ROOT);
        }
    }
}
