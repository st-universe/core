<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\SkillEnhancementInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\StuTestCase;

class CrewEnhancementTest extends StuTestCase
{
    /** @var MockInterface&SkillEnhancementCacheInterface */
    private $skillEnhancementCache;
    /** @var MockInterface&RaiseExpertise */
    private $raiseExpertise;

    /** @var MockInterface&SpacecraftInterface */
    private $target;

    private CrewEnhancementInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->skillEnhancementCache = $this->mock(SkillEnhancementCacheInterface::class);
        $this->raiseExpertise = $this->mock(RaiseExpertise::class);

        $this->target = $this->mock(SpacecraftInterface::class);

        $this->subject = new CrewEnhancement(
            $this->skillEnhancementCache,
            $this->raiseExpertise
        );
    }

    public function testAddExpertiseExpectNothingIfEnhancementsEmpty(): void
    {
        $this->skillEnhancementCache->shouldReceive('getSkillEnhancements')
            ->with(SkillEnhancementEnum::FINISH_ASTRO_MAPPING)
            ->once()
            ->andReturn(null);

        $this->subject->addExpertise($this->target, SkillEnhancementEnum::FINISH_ASTRO_MAPPING, 100);
    }

    public function testAddExpertiseExpectRaisingAndLogs(): void
    {
        $enhancement = $this->mock(SkillEnhancementInterface::class);
        $crewAssignmentWithoutPosition = $this->mock(CrewAssignmentInterface::class);
        $crewAssignmentCaptain = $this->mock(CrewAssignmentInterface::class);
        $crewAssignmentNewbieTechnician = $this->mock(CrewAssignmentInterface::class);
        $crewAssignmentSkilledTechnician = $this->mock(CrewAssignmentInterface::class);
        $crewNewbieTechnician = $this->mock(CrewInterface::class);
        $crewSkilledTechnician = $this->mock(CrewInterface::class);

        $this->skillEnhancementCache->shouldReceive('getSkillEnhancements')
            ->with(SkillEnhancementEnum::FINISH_ASTRO_MAPPING)
            ->once()
            ->andReturn([CrewPositionEnum::TECHNICAL->value => $enhancement]);

        $this->target->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([
                $crewAssignmentWithoutPosition,
                $crewAssignmentCaptain,
                $crewAssignmentNewbieTechnician,
                $crewAssignmentSkilledTechnician
            ]));

        $crewAssignmentWithoutPosition->shouldReceive('getPosition')
            ->withNoArgs()
            ->once()
            ->andReturn(null);
        $crewAssignmentCaptain->shouldReceive('getPosition')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewPositionEnum::CAPTAIN);
        $crewAssignmentNewbieTechnician->shouldReceive('getPosition')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewPositionEnum::TECHNICAL);
        $crewAssignmentSkilledTechnician->shouldReceive('getPosition')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewPositionEnum::TECHNICAL);

        $crewAssignmentNewbieTechnician->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crewNewbieTechnician);
        $crewAssignmentSkilledTechnician->shouldReceive('getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn($crewSkilledTechnician);

        $this->raiseExpertise->shouldReceive('raiseExpertise')
            ->with(
                $crewNewbieTechnician,
                $this->target,
                CrewPositionEnum::TECHNICAL,
                $enhancement,
                10
            );
        $this->raiseExpertise->shouldReceive('raiseExpertise')
            ->with(
                $crewSkilledTechnician,
                $this->target,
                CrewPositionEnum::TECHNICAL,
                $enhancement,
                10
            );

        $this->subject->addExpertise($this->target, SkillEnhancementEnum::FINISH_ASTRO_MAPPING, 10);
    }
}
