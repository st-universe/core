<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewPositionEnum;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\CrewAssignmentInterface;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\CrewSkillInterface;
use Stu\Orm\Entity\SkillEnhancementInterface;
use Stu\Orm\Entity\SkillEnhancementLogInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SkillEnhancementRepositoryInterface;
use Stu\StuTestCase;

class SkillEnhancementCacheTest extends StuTestCase
{
    /** @var MockInterface&SkillEnhancementRepositoryInterface */
    private $skillEnhancementRepository;

    private SkillEnhancementCacheInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->skillEnhancementRepository = $this->mock(SkillEnhancementRepositoryInterface::class);

        $this->subject = new SkillEnhancementCache(
            $this->skillEnhancementRepository
        );
    }

    public function testGetSkillEnhancements(): void
    {
        $captainEnhancementForDestruction = $this->mock(SkillEnhancementInterface::class);
        $commanderEnhancementForDestruction = $this->mock(SkillEnhancementInterface::class);

        $captainEnhancementForDestruction->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SkillEnhancementEnum::SPACECRAFT_DESTRUCTION);
        $commanderEnhancementForDestruction->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(SkillEnhancementEnum::SPACECRAFT_DESTRUCTION);

        $captainEnhancementForDestruction->shouldReceive('getPosition')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewPositionEnum::CAPTAIN);
        $commanderEnhancementForDestruction->shouldReceive('getPosition')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewPositionEnum::COMMAND);

        $this->skillEnhancementRepository->shouldReceive('findAll')
            ->withNoArgs()
            ->once()
            ->andReturn([
                $captainEnhancementForDestruction,
                $commanderEnhancementForDestruction
            ]);

        $resultAstro = $this->subject->getSkillEnhancements(SkillEnhancementEnum::FINISH_ASTRO_MAPPING);
        $resultDestruction = $this->subject->getSkillEnhancements(SkillEnhancementEnum::SPACECRAFT_DESTRUCTION);

        $this->assertNull($resultAstro);
        $this->assertEquals([
            CrewPositionEnum::CAPTAIN->value => $captainEnhancementForDestruction,
            CrewPositionEnum::COMMAND->value => $commanderEnhancementForDestruction
        ], $resultDestruction);
    }
}
