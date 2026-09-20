<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Lib\UserLockedException;
use Stu\Lib\Session\SessionInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLock;
use Stu\StuTestCase;

class UserLockCheckerTest extends StuTestCase
{
    public function testCheckReturnsForUnlockedUser(): void
    {
        $session = $this->mock(SessionInterface::class);
        $user = $this->mock(User::class);

        $user->shouldReceive('getUserLock')->once()->andReturnNull();
        $user->shouldReceive('isLocked')->once()->andReturnFalse();
        $session->shouldNotReceive('logout');

        (new UserLockChecker($session))->check($user);
    }

    public function testCheckLogsOutAndThrowsForLockedUser(): void
    {
        $session = $this->mock(SessionInterface::class);
        $user = $this->mock(User::class);
        $userLock = $this->mock(UserLock::class);

        $user->shouldReceive('getUserLock')->once()->andReturn($userLock);
        $user->shouldReceive('isLocked')->once()->andReturnTrue();
        $userLock->shouldReceive('getRemainingTicks')->once()->andReturn(3);
        $userLock->shouldReceive('getReason')->once()->andReturn('test reason');
        $session->shouldReceive('logout')->once();

        static::expectException(UserLockedException::class);

        (new UserLockChecker($session))->check($user);
    }
}
