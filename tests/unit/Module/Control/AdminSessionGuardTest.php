<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class AdminSessionGuardTest extends StuTestCase
{
    public function testCheckStartsAndValidatesAdminSession(): void
    {
        $sessionStarter = $this->mock(\Stu\Config\SessionStarterInterface::class);
        $session = $this->mock(SessionInterface::class);
        $stuConfig = $this->mock(StuConfigInterface::class);
        $user = $this->mock(User::class);

        $sessionStarter->shouldReceive('start')->once();
        $session->shouldReceive('createSession')->once();
        $session->shouldReceive('getUser')->once()->andReturn($user);
        $user->shouldReceive('getId')->once()->andReturn(42);
        $stuConfig->shouldReceive('getGameSettings->getAdminIds')
            ->once()
            ->andReturn([42]);

        (new AdminSessionGuard($sessionStarter, $session, $stuConfig))->check();
    }
}
