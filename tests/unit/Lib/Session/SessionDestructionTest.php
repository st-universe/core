<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\StuTestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SessionDestructionTest extends StuTestCase
{
    public function testAdminLogoutDoesNotInvalidatePlayersTokens(): void
    {
        session_start();
        $_SESSION = ['uid' => 1, 'login' => 1, 'impersonated_user_id' => 101];
        $repository = $this->mock(SessionStringRepositoryInterface::class);
        $repository->shouldNotReceive('truncate');
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $impersonation->shouldReceive('isActive')->once()->andReturn(true);
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('setUser')->with(null)->once()->andReturnSelf();

        new SessionDestruction($repository, $impersonation)->destroySession($session);

        self::assertSame([], $_SESSION);
        self::assertSame(PHP_SESSION_NONE, session_status());
    }
    public function testPlayerLogoutKeepsExistingTokenCleanup(): void
    {
        session_start();
        $_SESSION = ['uid' => 101, 'login' => 1];
        $user = $this->mock(User::class);
        $repository = $this->mock(SessionStringRepositoryInterface::class);
        $repository->shouldReceive('truncate')->with($user)->once();
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $impersonation->shouldReceive('isActive')->once()->andReturn(false);
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('getUser')->once()->andReturn($user);
        $session->shouldReceive('setUser')->with(null)->once()->andReturnSelf();

        new SessionDestruction($repository, $impersonation)->destroySession($session);

        self::assertSame([], $_SESSION);
        self::assertSame(PHP_SESSION_NONE, session_status());
    }

}
