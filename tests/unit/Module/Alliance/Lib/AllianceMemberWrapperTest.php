<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Mockery\MockInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class AllianceMemberWrapperTest extends StuTestCase
{
    private MockInterface&User $user;

    private MockInterface&Alliance $alliance;

    private MockInterface&AllianceJobManagerInterface $allianceJobManager;

    private AllianceMemberWrapper $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->user = $this->mock(User::class);
        $this->alliance = $this->mock(Alliance::class);
        $this->allianceJobManager = $this->mock(AllianceJobManagerInterface::class);

        $this->subject = new AllianceMemberWrapper(
            $this->user,
            $this->alliance,
            $this->allianceJobManager
        );
    }

    public function testGetUserReturnsData(): void
    {
        self::assertSame(
            $this->user,
            $this->subject->getUser()
        );
    }

    public function testGetAllianceReturnsData(): void
    {
        self::assertSame(
            $this->alliance,
            $this->subject->getAlliance()
        );
    }

    public function testIsFounderReturnsTrueIfSo(): void
    {
        $this->allianceJobManager->shouldReceive('hasUserPermission')
            ->with($this->user, $this->alliance, AllianceJobPermissionEnum::FOUNDER)
            ->once()
            ->andReturnTrue();

        self::assertTrue(
            $this->subject->isFounder()
        );
    }

    public function testIsFounderReturnsTrueIfIdsAreDifferent(): void
    {
        $this->allianceJobManager->shouldReceive('hasUserPermission')
            ->with($this->user, $this->alliance, AllianceJobPermissionEnum::FOUNDER)
            ->once()
            ->andReturnFalse();

        self::assertFalse(
            $this->subject->isFounder()
        );
    }

    public function testGetUserIdReturnsValue(): void
    {
        $value = 666;

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        self::assertSame(
            $value,
            $this->subject->getUserId()
        );
    }

    public function testGetOnlineStatusCssClassReturnsOnlineValue(): void
    {
        $this->user->shouldReceive('isOnline')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        self::assertSame(
            'online',
            $this->subject->getOnlineStateCssClass()
        );
    }

    public function testGetOnlineStatusCssClassReturnsOfflineValue(): void
    {
        $this->user->shouldReceive('isOnline')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        self::assertSame(
            'offline',
            $this->subject->getOnlineStateCssClass()
        );
    }
}
