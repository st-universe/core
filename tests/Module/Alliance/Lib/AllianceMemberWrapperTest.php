<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class AllianceMemberWrapperTest extends StuTestCase
{
    /** @var MockInterface&UserInterface */
    private MockInterface $user;

    /** @var MockInterface&AllianceInterface */
    private MockInterface $alliance;

    private AllianceMemberWrapper $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->user = $this->mock(UserInterface::class);
        $this->alliance = $this->mock(AllianceInterface::class);

        $this->subject = new AllianceMemberWrapper(
            $this->user,
            $this->alliance
        );
    }

    public function testGetUserReturnsData(): void
    {
        static::assertSame(
            $this->user,
            $this->subject->getUser()
        );
    }

    public function testGetAllianceReturnsData(): void
    {
        static::assertSame(
            $this->alliance,
            $this->subject->getAlliance()
        );
    }

    public function testIsFounderReturnsTrueIfSo(): void
    {
        $userId = 666;

        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->alliance->shouldReceive('getFounder->getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        static::assertTrue(
            $this->subject->isFounder()
        );
    }

    public function testIsFounderReturnsTrueIfIdsAreDifferent(): void
    {
        $this->user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->alliance->shouldReceive('getFounder->getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        static::assertFalse(
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

        static::assertSame(
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

        static::assertSame(
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

        static::assertSame(
            'offline',
            $this->subject->getOnlineStateCssClass()
        );
    }
}
