<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Mockery\MockInterface;
use Override;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\StuTestCase;

class AllianceUserApplicationCheckerTest extends StuTestCase
{
    private AllianceJobRepositoryInterface&MockInterface $allianceJobRepository;
    private User&MockInterface $user;

    private Alliance&MockInterface $alliance;

    private AllianceUserApplicationChecker $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->allianceJobRepository = $this->mock(AllianceJobRepositoryInterface::class);

        $this->user = $this->mock(User::class);
        $this->alliance = $this->mock(Alliance::class);

        $this->subject = new AllianceUserApplicationChecker(
            $this->allianceJobRepository
        );
    }

    public function testMayApplyReturnsFalseIfUserHasPendingApplications(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceJobTypeEnum::PENDING
            )
            ->once()
            ->andReturn($this->mock(AllianceJob::class));

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsFalseIfAllianceDoesNotAcceptApplications(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceJobTypeEnum::PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsFalseIfUserIsInAnAlliance(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceJobTypeEnum::PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Alliance::class));

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsFalseIfFactionsDontMatch(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceJobTypeEnum::PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFaction')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->mock(Faction::class));

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->user->shouldReceive('getFaction')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Faction::class));

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsTrueIfFactionsMatch(): void
    {
        $faction = $this->mock(Faction::class);

        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceJobTypeEnum::PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFaction')
            ->withNoArgs()
            ->twice()
            ->andReturn($faction);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->user->shouldReceive('getFaction')
            ->withNoArgs()
            ->once()
            ->andReturn($faction);

        $this->assertTrue(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsTrueIfFactionModeIsDisabled(): void
    {
        $this->allianceJobRepository->shouldReceive('getByUserAndAllianceAndType')
            ->with(
                $this->user,
                $this->alliance,
                AllianceJobTypeEnum::PENDING
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFaction')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->assertTrue(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }
}
