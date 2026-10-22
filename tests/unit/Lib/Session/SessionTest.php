<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLastAction;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserLastActionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class SessionTest extends StuTestCase
{
    public function testOnlyRealPlayerActivityUpdatesLastActionDuringParallelAccess(): void
    {
        session_id('admin-access-test');
        $admin = $this->mock(User::class);
        $player = $this->mock(User::class);
        $admin->shouldReceive('getId')->andReturn(1);
        $player->shouldReceive('getId')->andReturn(101);
        $admin->shouldReceive('getRegistration')->andReturn(new UserRegistration($admin)->setPassword('admin-hash'));
        $player->shouldReceive('getRegistration')->andReturn(new UserRegistration($player)->setPassword('player-hash'));
        $lastAction = new UserLastAction($player)->setTimestamp(100);
        $player->shouldReceive('getLastAction')->andReturn($lastAction);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('find')->with(1)->andReturn($admin);
        $users->shouldReceive('find')->with(101)->andReturn($player);
        $ips = $this->mock(UserIpTableRepositoryInterface::class);
        $ips->shouldReceive('findBySessionId')->with('admin-access-test')->andReturnNull();
        $lastActions = $this->mock(UserLastActionRepositoryInterface::class);
        $lastActions->shouldReceive('save')->with($lastAction)->once();
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $impersonation->shouldReceive('resolveUser')->with($admin, false)->andReturn($player);
        $impersonation->shouldReceive('resolveUser')->with($player, false)->andReturn($player);
        $impersonation->shouldReceive('isActive')->andReturnUsing(fn (): bool => isset($_SESSION['impersonated_user_id']));
        $session = new Session($ips, $users, $lastActions, $this->mock(SessionDestructionInterface::class), $impersonation);

        $_SESSION = ['uid' => 1, 'login' => 1, 'impersonated_user_id' => 101];
        SessionCredential::bind($_SESSION['uid'] === 1 ? $admin : $player);
        $session->createSession();
        self::assertSame($player, $session->getUser());
        self::assertSame(100, $lastAction->getTimestamp());

        $_SESSION = ['uid' => 101, 'login' => 1];
        SessionCredential::bind($_SESSION['uid'] === 1 ? $admin : $player);
        $session->createSession();
        $playerTimestamp = $lastAction->getTimestamp();
        self::assertGreaterThan(100, $playerTimestamp);

        $_SESSION = ['uid' => 1, 'login' => 1, 'impersonated_user_id' => 101];
        SessionCredential::bind($_SESSION['uid'] === 1 ? $admin : $player);
        $session->createSession();
        self::assertSame($playerTimestamp, $lastAction->getTimestamp());
    }

    public function testAdminUsesAndChangesPlayersStoredFleetState(): void
    {
        $player = $this->mock(User::class);
        $player->shouldReceive('getId')->andReturn(101);
        $data = serialize(['hiddenshiplistfleets' => [42 => 1]]);
        $player->shouldReceive('getSessiondata')->andReturnUsing(fn (): string => $data);
        $player->shouldReceive('setSessiondata')->once()->andReturnUsing(function (string $value) use (&$data, $player): User {
            $data = $value;
            return $player;
        });
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('save')->with($player)->once();
        $session = $this->mock(SessionInterface::class);
        $session->shouldReceive('getUser')->andReturn($player);
        $storage = new SessionStorage($users, $session);

        self::assertTrue($storage->hasSessionValue('hiddenshiplistfleets', 42));
        $storage->deleteSessionData('hiddenshiplistfleets', 42);
        self::assertSame(['hiddenshiplistfleets' => []], unserialize($data));
    }
}
