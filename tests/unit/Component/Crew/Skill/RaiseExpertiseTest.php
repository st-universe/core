<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\CrewSkillInterface;
use Stu\Orm\Entity\SkillEnhancementInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewSkillRepositoryInterface;
use Stu\StuTestCase;

class RaiseExpertiseTest extends StuTestCase
{
    /** @var MockInterface&CrewSkillRepositoryInterface */
    private $crewSkillRepository;
    /** @var MockInterface&CrewRepositoryInterface */
    private $crewRepository;
    /** @var MockInterface&CreateEnhancementLog */
    private $createEnhancementLog;

    /** @var MockInterface&SpacecraftInterface */
    private $target;

    private RaiseExpertise $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->crewSkillRepository = $this->mock(CrewSkillRepositoryInterface::class);
        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->createEnhancementLog = $this->mock(CreateEnhancementLog::class);

        $this->subject = new RaiseExpertise(
            $this->crewSkillRepository,
            $this->crewRepository,
            $this->createEnhancementLog
        );
    }

    public function testRaiseExpertiseExpectSkillCreationWhenUnskilled(): void
    {
        $spacecraft = $this->mock(SpacecraftInterface::class);
        $enhancement = $this->mock(SkillEnhancementInterface::class);
        $crewNewbieTechnician = $this->mock(CrewInterface::class);
        $newSkill = $this->mock(CrewSkillInterface::class);

        $skillsNewbieTechnician = new ArrayCollection();

        $spacecraft->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $crewNewbieTechnician->shouldReceive('getSkills')
            ->withNoArgs()
            ->once()
            ->andReturn($skillsNewbieTechnician);

        $enhancement->shouldReceive('getExpertise')
            ->withNoArgs()
            ->andReturn(42);

        $newSkill->shouldReceive('setCrew')
            ->with($crewNewbieTechnician)
            ->once()
            ->andReturnSelf();
        $newSkill->shouldReceive('setPosition')
            ->with(CrewPositionEnum::TECHNICAL)
            ->once()
            ->andReturnSelf();
        $newSkill->shouldReceive('getRank')
            ->withNoArgs()
            ->andReturn(CrewSkillLevelEnum::RECRUIT);

        $newSkill->shouldReceive('increaseExpertise')
            ->with(5)
            ->once();
        $newSkill->shouldReceive('getExpertise')
            ->withNoArgs()
            ->andReturn(5);

        $this->crewSkillRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($newSkill);
        $this->crewSkillRepository->shouldReceive('save')
            ->with($newSkill)
            ->once();

        $this->createEnhancementLog->shouldReceive('createEnhancementLog')
            ->with(
                $newSkill,
                'SHIP',
                $enhancement,
                5,
                CrewSkillLevelEnum::RECRUIT
            )
            ->once();

        $this->subject->raiseExpertise(
            $crewNewbieTechnician,
            $spacecraft,
            CrewPositionEnum::TECHNICAL,
            $enhancement,
            10
        );

        $this->assertEquals([CrewPositionEnum::TECHNICAL->value => $newSkill], $skillsNewbieTechnician->toArray());
    }

    public function testRaiseExpertiseExpectSkillEnhancementWhenSkilled(): void
    {
        $spacecraft = $this->mock(SpacecraftInterface::class);
        $enhancement = $this->mock(SkillEnhancementInterface::class);
        $crewSkilledTechnician = $this->mock(CrewInterface::class);
        $existingSkill = $this->mock(CrewSkillInterface::class);

        $skillsSkilledTechnician = new ArrayCollection([CrewPositionEnum::TECHNICAL->value => $existingSkill]);

        $spacecraft->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('SHIP');

        $crewSkilledTechnician->shouldReceive('getSkills')
            ->withNoArgs()
            ->once()
            ->andReturn($skillsSkilledTechnician);
        $crewSkilledTechnician->shouldReceive('setRank')
            ->with(CrewSkillLevelEnum::CADET)
            ->once();

        $enhancement->shouldReceive('getExpertise')
            ->withNoArgs()
            ->andReturn(50);

        $existingSkill->shouldReceive('increaseExpertise')
            ->with(50)
            ->once();
        $existingSkill->shouldReceive('getExpertise')
            ->withNoArgs()
            ->andReturn(77);
        $existingSkill->shouldReceive('getRank')
            ->withNoArgs()
            ->andReturn(CrewSkillLevelEnum::RECRUIT, CrewSkillLevelEnum::CADET, CrewSkillLevelEnum::CADET);

        $this->crewSkillRepository->shouldReceive('save')
            ->with($existingSkill)
            ->once();

        $this->crewRepository->shouldReceive('save')
            ->with($crewSkilledTechnician)
            ->once();

        $this->createEnhancementLog->shouldReceive('createEnhancementLog')
            ->with(
                $existingSkill,
                'SHIP',
                $enhancement,
                50,
                CrewSkillLevelEnum::RECRUIT
            )
            ->once();

        $this->subject->raiseExpertise(
            $crewSkilledTechnician,
            $spacecraft,
            CrewPositionEnum::TECHNICAL,
            $enhancement,
            100
        );
        $this->assertEquals([CrewPositionEnum::TECHNICAL->value => $existingSkill], $skillsSkilledTechnician->toArray());
    }
}
