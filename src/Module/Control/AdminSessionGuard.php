<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use request;
use Stu\Config\SessionStarterInterface;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;

final class AdminSessionGuard implements AdminSessionGuardInterface
{
    private const string REDIRECT_TO_DOMAIN_ROOT = 'Location: /';

    public function __construct(
        private readonly SessionStarterInterface $sessionStarter,
        private readonly SessionInterface $session,
        private readonly StuConfigInterface $stuConfig,
        private readonly SessionStringRepositoryInterface $sessionStringRepository
    ) {}

    #[\Override]
    public function check(): User
    {
        $this->sessionStarter->start();
        $this->session->createSession(true);

        $user = $this->session->getUser();
        $adminIds = $this->stuConfig->getGameSettings()->getAdminIds();
        if ($user === null || !in_array($user->getId(), $adminIds, true)) {
            header(self::REDIRECT_TO_DOMAIN_ROOT);
            exit;
        }

        if (request::isPost()) {
            $token = request::postString('sstr');
            if (!$token || !$this->sessionStringRepository->isValid($token, $user->getId())) {
                http_response_code(403);
                exit;
            }
        }

        return $user;
    }
}
