<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\SessionInvalidException;
use Stu\Module\Control\GameSessionInitializer;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLastAction;
use Stu\Orm\Entity\UserRegistration;
use Stu\Orm\Repository\UserIpTableRepositoryInterface;
use Stu\Orm\Repository\UserLastActionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class BlockedSessionTest extends StuTestCase
{
    public static function accountStates(): iterable
    {
        foreach ([ModuleEnum::GAME, ModuleEnum::OPTIONS] as $module) {
            foreach ([0, UserConstants::DELETION_REQUESTED, UserConstants::DELETION_CONFIRMED, UserConstants::DELETION_FORBIDDEN] as $deletionMark) {
                yield $module->name . '-' . $deletionMark => [$module, $deletionMark];
            }
        }
    }

    #[DataProvider('accountStates')]
    public function testExistingSessionRejectsConfirmedDeletionBeforeModuleActivity(ModuleEnum $module, int $deletionMark): void
    {
        session_id('blocked-session-test');
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(101);
        $registration = new UserRegistration($user)->setPassword('password-hash')->setDeletionMark($deletionMark);
        $user->shouldReceive('getRegistration')->andReturn($registration);
        $users = $this->mock(UserRepositoryInterface::class);
        $users->shouldReceive('find')->with(101)->once()->andReturn($user);
        $ips = $this->mock(UserIpTableRepositoryInterface::class);
        $lastActions = $this->mock(UserLastActionRepositoryInterface::class);
        $destruction = $this->mock(SessionDestructionInterface::class);
        $impersonation = $this->mock(AdminImpersonationInterface::class);
        $allowed = $deletionMark !== UserConstants::DELETION_CONFIRMED;
        if ($allowed) {
            $lastAction = new UserLastAction($user);
            $user->shouldReceive('getLastAction')->andReturn($lastAction);
            $ips->shouldReceive('findBySessionId')->with('blocked-session-test')->once()->andReturnNull();
            $lastActions->shouldReceive('save')->with($lastAction)->once();
            $impersonation->shouldReceive('resolveUser')->with($user, false)->once()->andReturn($user);
            $impersonation->shouldReceive('isActive')->once()->andReturnFalse();
            $destruction->shouldNotReceive('destroySession');
        } else {
            $ips->shouldNotReceive('findBySessionId');
            $lastActions->shouldNotReceive('save');
            $impersonation->shouldNotReceive('resolveUser');
            $destruction->shouldReceive('destroySession')->once();
        }
        $_SESSION = ['uid' => 101, 'login' => 1];
        SessionCredential::bind($user);
        $session = new Session($ips, $users, $lastActions, $destruction, $impersonation);
        $initializer = new GameSessionInitializer($session, $this->mock(SessionLoginInterface::class));
        if ($allowed) {
            self::assertSame($user, $initializer->initialize($module));
        } else {
            $this->expectException(SessionInvalidException::class);
            $initializer->initialize($module);
        }
    }
}
