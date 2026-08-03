<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewSkill;
use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Entity\SkillEnhancementLog;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewSkillRepositoryInterface;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;
use Stu\StuTestCase;

final class RaiseExpertiseTest extends StuTestCase
{
    private MockInterface&CrewSkillRepositoryInterface $crewSkillRepository;

    private MockInterface&CrewRepositoryInterface $crewRepository;

    private MockInterface&SkillEnhancementLogRepositoryInterface $logRepository;

    private MockInterface&StuTime $stuTime;

    private RaiseExpertise $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->crewSkillRepository = $this->mock(CrewSkillRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->logRepository = $this->mock(SkillEnhancementLogRepositoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new RaiseExpertise(
            $this->crewSkillRepository,
            $this->crewRepository,
            new CreateEnhancementLog($this->logRepository, $this->stuTime)
        );
    }

    public function testPromotesCrewWhenSkillReachesHigherRank(): void
    {
        $crew = $this->createCrew(CrewSkillLevelEnum::CADET);
        $skill = new CrewSkill()
            ->setCrew($crew)
            ->setPosition(CrewTypeEnum::SCIENCE);
        $crew->shouldReceive('getSkills')->andReturn(new ArrayCollection([
            CrewTypeEnum::SCIENCE->value => $skill
        ]));
        $crew->shouldReceive('setRank')->with(CrewSkillLevelEnum::CREWMAN)->once();

        $this->crewSkillRepository->shouldReceive('save')->with($skill)->once();
        $this->crewRepository->shouldReceive('save')->with($crew)->once();
        $this->expectLog($skill, 100, 100, CrewSkillLevelEnum::CADET, CrewSkillLevelEnum::CREWMAN);

        $this->subject->raiseExpertise(
            $crew,
            $this->createSpacecraft(),
            CrewTypeEnum::SCIENCE,
            $this->createEnhancement(100),
            100
        );

        static::assertSame(100, $skill->getExpertise());
        static::assertSame(CrewSkillLevelEnum::CREWMAN, $skill->getRank());
    }

    public function testDoesNotLowerCrewRankForLowerRankSkill(): void
    {
        $crew = $this->createCrew(CrewSkillLevelEnum::COMMANDER);
        $skill = new CrewSkill()
            ->setCrew($crew)
            ->setPosition(CrewTypeEnum::SCIENCE);
        $skill->increaseExpertise(99);
        $crew->shouldReceive('getSkills')->andReturn(new ArrayCollection([
            CrewTypeEnum::SCIENCE->value => $skill
        ]));
        $crew->shouldReceive('setRank')->never();

        $this->crewSkillRepository->shouldReceive('save')->with($skill)->once();
        $this->crewRepository->shouldReceive('save')->never();
        $this->expectLog($skill, 1, 100, CrewSkillLevelEnum::COMMANDER, CrewSkillLevelEnum::COMMANDER);

        $this->subject->raiseExpertise(
            $crew,
            $this->createSpacecraft(),
            CrewTypeEnum::SCIENCE,
            $this->createEnhancement(1),
            100
        );

        static::assertSame(100, $skill->getExpertise());
        static::assertSame(CrewSkillLevelEnum::CREWMAN, $skill->getRank());
    }

    public function testDoesNotAutomaticallyPromoteBeyondLieutenantCommander(): void
    {
        $crew = $this->createCrew(CrewSkillLevelEnum::LIEUTENANT_COMMANDER);
        $skill = new CrewSkill()
            ->setCrew($crew)
            ->setPosition(CrewTypeEnum::SCIENCE);
        $crew->shouldReceive('getSkills')->andReturn(new ArrayCollection([
            CrewTypeEnum::SCIENCE->value => $skill
        ]));
        $crew->shouldReceive('setRank')->never();

        $this->crewSkillRepository->shouldReceive('save')->with($skill)->once();
        $this->crewRepository->shouldReceive('save')->never();
        $this->expectLog(
            $skill,
            10_000,
            10_000,
            CrewSkillLevelEnum::LIEUTENANT_COMMANDER,
            CrewSkillLevelEnum::LIEUTENANT_COMMANDER
        );

        $this->subject->raiseExpertise(
            $crew,
            $this->createSpacecraft(),
            CrewTypeEnum::SCIENCE,
            $this->createEnhancement(10_000),
            100
        );

        static::assertSame(CrewSkillLevelEnum::COMMANDER, $skill->getRank());
    }

    public function testPromotesCrewToLieutenantCommanderWhenExperienceExceedsAutomaticRanks(): void
    {
        $crew = $this->createCrew(CrewSkillLevelEnum::CADET);
        $skill = new CrewSkill()
            ->setCrew($crew)
            ->setPosition(CrewTypeEnum::SCIENCE);
        $crew->shouldReceive('getSkills')->andReturn(new ArrayCollection([
            CrewTypeEnum::SCIENCE->value => $skill
        ]));
        $crew->shouldReceive('setRank')->with(CrewSkillLevelEnum::LIEUTENANT_COMMANDER)->once();

        $this->crewSkillRepository->shouldReceive('save')->with($skill)->once();
        $this->crewRepository->shouldReceive('save')->with($crew)->once();
        $this->expectLog(
            $skill,
            10_000,
            10_000,
            CrewSkillLevelEnum::CADET,
            CrewSkillLevelEnum::LIEUTENANT_COMMANDER
        );

        $this->subject->raiseExpertise(
            $crew,
            $this->createSpacecraft(),
            CrewTypeEnum::SCIENCE,
            $this->createEnhancement(10_000),
            100
        );

        static::assertSame(CrewSkillLevelEnum::COMMANDER, $skill->getRank());
    }

    /** @return MockInterface&Crew */
    private function createCrew(CrewSkillLevelEnum $rank): Crew
    {
        $crew = $this->mock(Crew::class);
        $crew->shouldReceive('getRank')->andReturn($rank);
        $crew->shouldReceive('getId')->andReturn(42);
        $crew->shouldReceive('getName')->andReturn('Crew');
        $user = $this->mock(User::class);
        $user->shouldReceive('getCrewRankName')->andReturnUsing(
            fn (CrewSkillLevelEnum $rank): string => $rank->getDescription(1)
        );
        $crew->shouldReceive('getUser')->andReturn($user);

        return $crew;
    }

    private function createSpacecraft(): Spacecraft
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $spacecraft->shouldReceive('getName')->andReturn('Ship');

        return $spacecraft;
    }

    private function createEnhancement(int $expertise): SkillEnhancement
    {
        $enhancement = $this->mock(SkillEnhancement::class);
        $enhancement->shouldReceive('getExpertise')->andReturn($expertise);

        return $enhancement;
    }

    private function expectLog(
        CrewSkill $skill,
        int $amount,
        int $expertiseSum,
        CrewSkillLevelEnum $oldRank,
        CrewSkillLevelEnum $newRank
    ): void {
        $log = $this->mock(SkillEnhancementLog::class);
        $this->logRepository->shouldReceive('prototype')->once()->andReturn($log);
        $this->logRepository->shouldReceive('save')->with($log)->once();
        $this->stuTime->shouldReceive('time')->once()->andReturn(1_234);

        $log->shouldReceive('setUser')->once()->andReturnSelf();
        $log->shouldReceive('setEnhancement')->once()->andReturnSelf();
        $log->shouldReceive('setCrewName')->once()->andReturnSelf();
        $log->shouldReceive('setShipName')->once()->andReturnSelf();
        $log->shouldReceive('setCrewId')->once()->andReturnSelf();
        $log->shouldReceive('setExpertise')->with($amount)->once()->andReturnSelf();
        $log->shouldReceive('setExpertiseSum')->with($expertiseSum)->once()->andReturnSelf();
        $log->shouldReceive('setPromotion')
            ->with(
                $oldRank === $newRank
                    ? null
                    : sprintf('Befoerderung %s -> %s', $oldRank->getDescription(1), $newRank->getDescription(1))
            )
            ->once()
            ->andReturnSelf();
        $log->shouldReceive('setTimestamp')->once()->andReturnSelf();
    }
}
