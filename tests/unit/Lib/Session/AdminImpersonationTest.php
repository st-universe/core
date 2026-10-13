<?php

declare(strict_types=1);

namespace Stu\Lib\Session;

use Mockery\MockInterface;
use Stu\Exception\SessionInvalidException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class AdminImpersonationTest extends StuTestCase
{
    private AdminImpersonation $subject;
    private UserRepositoryInterface&MockInterface $users;
    private StuConfigInterface&MockInterface $config;

    protected function setUp(): void
    {
        $_SESSION = ['uid' => 1, 'login' => 1];
        $this->users = $this->mock(UserRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);
        $this->config->shouldReceive('getGameSettings->getAdminIds')->andReturn([1]);
        $this->subject = new AdminImpersonation($this->users, $this->config);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testStartKeepsAuthenticatedAdminAndResolvesPlayer(): void
    {
        $admin = $this->user(1);
        $player = $this->user(101);
        $this->users->shouldReceive('find')->with(101)->once()->andReturn($player);
        $player->shouldNotReceive('setVacationMode');
        $player->shouldNotReceive('setState');
        $player->shouldNotReceive('setSessiondata');
        $this->users->shouldNotReceive('save');

        $this->subject->start($admin, $player);

        self::assertSame(1, $_SESSION['uid']);
        self::assertSame(1, $this->subject->getAdminId());
        self::assertSame(101, $this->subject->getTargetId());
        self::assertSame($player, $this->subject->resolveUser($admin, false));
        self::assertSame($admin, $this->subject->resolveUser($admin, true));

        $this->subject->stop();

        self::assertFalse($this->subject->isActive());
        self::assertSame(1, $_SESSION['uid']);
        self::assertSame($admin, $this->subject->resolveUser($admin, false));
    }

    public function testAdminCanAccessNpcAndReturnToAdminContext(): void
    {
        $admin = $this->user(1);
        $npc = $this->user(10);
        $this->users->shouldReceive('find')->with(10)->once()->andReturn($npc);

        $this->subject->start($admin, $npc);

        self::assertSame(1, $_SESSION['uid']);
        self::assertSame(10, $this->subject->getTargetId());
        self::assertSame($npc, $this->subject->resolveUser($admin, false));
        self::assertSame($admin, $this->subject->resolveUser($admin, true));
    }

    public function testSystemAccountCannotBeOpened(): void
    {
        $this->expectException(SessionInvalidException::class);

        $this->subject->start($this->user(1), $this->user(4));
    }

    public function testNonAdminCannotStartAccess(): void
    {
        $_SESSION['uid'] = 101;
        $this->expectException(SessionInvalidException::class);

        $this->subject->start($this->user(101), $this->user(102));
    }

    public function testCannotUseAnotherAdminsIdentity(): void
    {
        $_SESSION['uid'] = 101;
        $this->expectException(SessionInvalidException::class);

        $this->subject->start($this->user(1), $this->user(102));
    }

    public function testRevokedPermissionIsCheckedOnEveryRequest(): void
    {
        $_SESSION['uid'] = 2;
        $_SESSION['impersonated_user_id'] = 101;
        $this->users->shouldNotReceive('find');
        $this->expectException(SessionInvalidException::class);

        $this->subject->resolveUser($this->user(2), false);
    }

    public function testLockedTargetCannotBeOpened(): void
    {
        $this->expectException(SessionInvalidException::class);

        $this->subject->start($this->user(1), $this->user(101, true));
    }

    public function testAdminAreaRemainsAccessibleWhenTargetWasDeleted(): void
    {
        $_SESSION['impersonated_user_id'] = 101;
        $admin = $this->user(1);
        $this->users->shouldNotReceive('find');

        self::assertSame($admin, $this->subject->resolveUser($admin, true));
    }

    private function user(int $id, bool $locked = false): User&MockInterface
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn($id);
        $user->shouldReceive('isLocked')->andReturn($locked);
        $user->shouldReceive('isNpc')->andReturn(User::isUserNpc($id));
        $user->shouldReceive('getState')->andReturn(UserStateEnum::ACTIVE);
        $user->shouldReceive('getRegistration->getDeletionMark')->andReturn(0);

        return $user;
    }
}
