<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\CrewSkillInterface;
use Stu\Orm\Entity\SkillEnhancementInterface;
use Stu\Orm\Entity\SkillEnhancementLogInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;
use Stu\StuTestCase;

class CreateEnhancementLogTest extends StuTestCase
{
    /** @var MockInterface&SkillEnhancementLogRepositoryInterface */
    private $skillEnhancementLogRepository;
    /** @var MockInterface&StuTime */
    private $stuTime;

    private CreateEnhancementLog $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->skillEnhancementLogRepository = $this->mock(SkillEnhancementLogRepositoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new CreateEnhancementLog(
            $this->skillEnhancementLogRepository,
            $this->stuTime
        );
    }

    public function testCreateEnhancementLogWithoutPromotion(): void
    {
        $enhancement = $this->mock(SkillEnhancementInterface::class);
        $crew = $this->mock(CrewInterface::class);
        $crewSkill = $this->mock(CrewSkillInterface::class);
        $log = $this->mock(SkillEnhancementLogInterface::class);
        $user = $this->mock(UserInterface::class);

        $crew->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $crew->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('CREW');
        $crew->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $crewSkill->shouldReceive('getExpertise')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $crewSkill->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew);
        $crewSkill->shouldReceive('getRank')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewSkillLevelEnum::CADET);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(123123);

        $log->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setEnhancement')
            ->with($enhancement)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setCrewName')
            ->with('CREW')
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setShipName')
            ->with('SHIP')
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setCrewId')
            ->with(123)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setExpertise')
            ->with(5)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setExpertiseSum')
            ->with(42)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setPromotion')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setTimestamp')
            ->with(123123)
            ->once()
            ->andReturnSelf();

        $this->skillEnhancementLogRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($log);
        $this->skillEnhancementLogRepository->shouldReceive('save')
            ->with($log)
            ->once();

        $this->subject->createEnhancementLog(
            $crewSkill,
            'SHIP',
            $enhancement,
            5,
            CrewSkillLevelEnum::CADET
        );
    }

    public function testCreateEnhancementLogWithPromotion(): void
    {
        $enhancement = $this->mock(SkillEnhancementInterface::class);
        $crew = $this->mock(CrewInterface::class);
        $crewSkill = $this->mock(CrewSkillInterface::class);
        $log = $this->mock(SkillEnhancementLogInterface::class);
        $user = $this->mock(UserInterface::class);

        $crew->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(123);
        $crew->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('CREW');
        $crew->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $crewSkill->shouldReceive('getExpertise')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $crewSkill->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crew);
        $crewSkill->shouldReceive('getRank')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewSkillLevelEnum::ENSIGN);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(123123);

        $log->shouldReceive('setUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setEnhancement')
            ->with($enhancement)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setCrewName')
            ->with('CREW')
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setShipName')
            ->with('SHIP')
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setCrewId')
            ->with(123)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setExpertise')
            ->with(5)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setExpertiseSum')
            ->with(42)
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setPromotion')
            ->with('Beförderung Kadett -> Fähnrich')
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setTimestamp')
            ->with(123123)
            ->once()
            ->andReturnSelf();

        $this->skillEnhancementLogRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($log);
        $this->skillEnhancementLogRepository->shouldReceive('save')
            ->with($log)
            ->once();

        $this->subject->createEnhancementLog(
            $crewSkill,
            'SHIP',
            $enhancement,
            5,
            CrewSkillLevelEnum::CADET
        );
    }
}
