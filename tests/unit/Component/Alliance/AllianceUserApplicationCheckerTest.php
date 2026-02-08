<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Mockery\MockInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceApplication;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;
use Stu\StuTestCase;

class AllianceUserApplicationCheckerTest extends StuTestCase
{
    private AllianceApplicationRepositoryInterface&MockInterface $allianceApplicationRepository;
    private User&MockInterface $user;

    private Alliance&MockInterface $alliance;

    private AllianceUserApplicationChecker $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->allianceApplicationRepository = $this->mock(AllianceApplicationRepositoryInterface::class);

        $this->user = $this->mock(User::class);
        $this->alliance = $this->mock(Alliance::class);

        $this->subject = new AllianceUserApplicationChecker(
            $this->allianceApplicationRepository
        );
    }

    public function testMayApplyReturnsFalseIfUserHasPendingApplications(): void
    {
        $this->allianceApplicationRepository->shouldReceive('getByUserAndAlliance')
            ->with(
                $this->user,
                $this->alliance
            )
            ->once()
            ->andReturn($this->mock(AllianceApplication::class));

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsFalseIfAllianceDoesNotAcceptApplications(): void
    {
        $this->allianceApplicationRepository->shouldReceive('getByUserAndAlliance')
            ->with(
                $this->user,
                $this->alliance
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
        $this->allianceApplicationRepository->shouldReceive('getByUserAndAlliance')
            ->with(
                $this->user,
                $this->alliance
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
        $faction = $this->mock(Faction::class);
        $otherFaction = $this->mock(Faction::class);

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);
        $otherFaction->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(2222);

        $this->allianceApplicationRepository->shouldReceive('getByUserAndAlliance')
            ->with(
                $this->user,
                $this->alliance
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFaction')
            ->withNoArgs()
            ->andReturn($faction);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->user->shouldReceive('getFaction')
            ->withNoArgs()
            ->andReturn($otherFaction);

        $this->assertFalse(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsTrueIfFactionsMatch(): void
    {
        $faction = $this->mock(Faction::class);

        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1111);

        $this->allianceApplicationRepository->shouldReceive('getByUserAndAlliance')
            ->with(
                $this->user,
                $this->alliance
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFaction')
            ->withNoArgs()
            ->andReturn($faction);

        $this->user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->user->shouldReceive('getFaction')
            ->withNoArgs()
            ->andReturn($faction);

        $this->assertTrue(
            $this->subject->mayApply($this->user, $this->alliance)
        );
    }

    public function testMayApplyReturnsTrueIfFactionModeIsDisabled(): void
    {
        $this->allianceApplicationRepository->shouldReceive('getByUserAndAlliance')
            ->with(
                $this->user,
                $this->alliance
            )
            ->once()
            ->andReturnNull();

        $this->alliance->shouldReceive('getAcceptApplications')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $this->alliance->shouldReceive('getFaction')
            ->withNoArgs()
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
