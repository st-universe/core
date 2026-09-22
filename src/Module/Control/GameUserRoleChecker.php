<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;

final class GameUserRoleChecker implements GameUserRoleCheckerInterface
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly StuConfigInterface $stuConfig
    ) {}

    #[\Override]
    public function isAdmin(): bool
    {
        $user = $this->session->getUser();

        return $user !== null
            && in_array(
                $user->getId(),
                $this->stuConfig->getGameSettings()->getAdminIds(),
                true
            );
    }

    #[\Override]
    public function isNpc(): bool
    {
        return $this->session->getUser()?->isNpc() ?? false;
    }
}
