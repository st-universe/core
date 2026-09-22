<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\Session\SessionInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class GameUserRoleCheckerTest extends StuTestCase
{
    public function testIsAdminReturnsFalseWithoutUser(): void
    {
        $session = $this->mock(SessionInterface::class);
        $stuConfig = $this->mock(StuConfigInterface::class);
        $session->shouldReceive('getUser')->once()->andReturnNull();
        $stuConfig->shouldNotReceive('getGameSettings');

        $this->assertFalse((new GameUserRoleChecker($session, $stuConfig))->isAdmin());
    }

    public function testIsAdminReturnsTrueForConfiguredAdmin(): void
    {
        $session = $this->mock(SessionInterface::class);
        $stuConfig = $this->mock(StuConfigInterface::class);
        $user = $this->mock(User::class);
        $session->shouldReceive('getUser')->once()->andReturn($user);
        $user->shouldReceive('getId')->once()->andReturn(42);
        $stuConfig->shouldReceive('getGameSettings->getAdminIds')->once()->andReturn([42]);

        $this->assertTrue((new GameUserRoleChecker($session, $stuConfig))->isAdmin());
    }

    public function testIsAdminReturnsFalseForUnconfiguredUser(): void
    {
        $session = $this->mock(SessionInterface::class);
        $stuConfig = $this->mock(StuConfigInterface::class);
        $user = $this->mock(User::class);
        $session->shouldReceive('getUser')->once()->andReturn($user);
        $user->shouldReceive('getId')->once()->andReturn(42);
        $stuConfig->shouldReceive('getGameSettings->getAdminIds')->once()->andReturn([7]);

        $this->assertFalse((new GameUserRoleChecker($session, $stuConfig))->isAdmin());
    }

    public function testIsNpcReturnsFalseWithoutUser(): void
    {
        $session = $this->mock(SessionInterface::class);
        $stuConfig = $this->mock(StuConfigInterface::class);
        $session->shouldReceive('getUser')->once()->andReturnNull();

        $this->assertFalse((new GameUserRoleChecker($session, $stuConfig))->isNpc());
    }

    public function testIsNpcReflectsUserRole(): void
    {
        $session = $this->mock(SessionInterface::class);
        $stuConfig = $this->mock(StuConfigInterface::class);
        $user = $this->mock(User::class);
        $session->shouldReceive('getUser')->once()->andReturn($user);
        $user->shouldReceive('isNpc')->once()->andReturnTrue();

        $this->assertTrue((new GameUserRoleChecker($session, $stuConfig))->isNpc());
    }
}
