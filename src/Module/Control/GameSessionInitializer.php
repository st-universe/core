<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionLoginInterface;
use Stu\Orm\Entity\User;

final class GameSessionInitializer implements GameSessionInitializerInterface
{
    public function __construct(
        private readonly SessionInterface $session,
        private readonly SessionLoginInterface $sessionLogin
    ) {}

    #[\Override]
    public function initialize(ModuleEnum $module): ?User
    {
        if ($module->doSessionCheck()) {
            $this->session->createSession();
        } else {
            $this->sessionLogin->checkLoginCookie();
        }

        return $this->session->getUser();
    }
}
