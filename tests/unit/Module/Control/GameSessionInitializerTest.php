<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Session\SessionInterface;
use Stu\Lib\Session\SessionLoginInterface;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class GameSessionInitializerTest extends StuTestCase
{
    public function testInitializeCreatesSessionForGameModule(): void
    {
        $session = $this->mock(SessionInterface::class);
        $sessionLogin = $this->mock(SessionLoginInterface::class);
        $user = $this->mock(User::class);

        $session->shouldReceive('createSession')->once();
        $session->shouldReceive('getUser')->once()->andReturn($user);
        $sessionLogin->shouldNotReceive('checkLoginCookie');

        $result = (new GameSessionInitializer($session, $sessionLogin))
            ->initialize(ModuleEnum::GAME);

        $this->assertSame($user, $result);
    }

    public function testInitializeChecksLoginCookieForIndexModule(): void
    {
        $session = $this->mock(SessionInterface::class);
        $sessionLogin = $this->mock(SessionLoginInterface::class);

        $session->shouldNotReceive('createSession');
        $session->shouldReceive('getUser')->once()->andReturnNull();
        $sessionLogin->shouldReceive('checkLoginCookie')->once();

        $result = (new GameSessionInitializer($session, $sessionLogin))
            ->initialize(ModuleEnum::INDEX);

        $this->assertNull($result);
    }
}
