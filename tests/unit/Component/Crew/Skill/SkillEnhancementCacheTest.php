<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Override;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Repository\SkillEnhancementRepositoryInterface;
use Stu\StuTestCase;

final class SkillEnhancementCacheTest extends StuTestCase
{
    private MockInterface&SkillEnhancementRepositoryInterface $repository;

    private MockInterface&EntityManagerInterface $entityManager;

    private SkillEnhancementCache $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->mock(SkillEnhancementRepositoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);
        $this->subject = new SkillEnhancementCache($this->repository, $this->entityManager);
    }

    public function testGroupsEnhancementsByTriggerAndPositionAndReusesManagedEntities(): void
    {
        $maintenance = new SkillEnhancement()
            ->setType(SkillEnhancementEnum::MAINTENANCE_TICK)
            ->setPosition(CrewTypeEnum::SCIENCE);
        $repair = new SkillEnhancement()
            ->setType(SkillEnhancementEnum::REPAIR_COMPLETED)
            ->setPosition(CrewTypeEnum::TECHNICAL);

        $this->repository->shouldReceive('findAll')->once()->andReturn([$maintenance, $repair]);
        $this->entityManager->shouldReceive('contains')->with($maintenance)->once()->andReturn(true);
        $this->entityManager->shouldReceive('contains')->with($repair)->once()->andReturn(true);

        self::assertSame(
            [CrewTypeEnum::SCIENCE->value => $maintenance],
            $this->subject->getSkillEnhancements(SkillEnhancementEnum::MAINTENANCE_TICK)
        );
        self::assertSame(
            [CrewTypeEnum::SCIENCE->value => $maintenance],
            $this->subject->getSkillEnhancements(SkillEnhancementEnum::MAINTENANCE_TICK)
        );
        self::assertSame(
            [CrewTypeEnum::TECHNICAL->value => $repair],
            $this->subject->getSkillEnhancements(SkillEnhancementEnum::REPAIR_COMPLETED)
        );
        self::assertNull($this->subject->getSkillEnhancements(SkillEnhancementEnum::BEAM_COMMODITIES));
    }

    public function testCachesEmptyResults(): void
    {
        $this->repository->shouldReceive('findAll')->once()->andReturn([]);
        $this->entityManager->shouldNotReceive('contains');

        self::assertNull($this->subject->getSkillEnhancements(SkillEnhancementEnum::MAINTENANCE_TICK));
        self::assertNull($this->subject->getSkillEnhancements(SkillEnhancementEnum::MAINTENANCE_TICK));
    }

    public function testReloadsAllEnhancementsWhenCachedEntityIsDetached(): void
    {
        $detached = new SkillEnhancement()
            ->setType(SkillEnhancementEnum::MAINTENANCE_TICK)
            ->setPosition(CrewTypeEnum::SCIENCE);
        $detachedRepair = new SkillEnhancement()
            ->setType(SkillEnhancementEnum::REPAIR_COMPLETED)
            ->setPosition(CrewTypeEnum::TECHNICAL);
        $managed = clone $detached;
        $managedRepair = clone $detachedRepair;

        $this->repository->shouldReceive('findAll')->twice()->andReturn(
            [$detached, $detachedRepair],
            [$managed, $managedRepair]
        );
        $this->entityManager->shouldReceive('contains')->with($detached)->once()->andReturn(false);
        $this->entityManager->shouldReceive('contains')->with($managed)->once()->andReturn(true);
        $this->entityManager->shouldReceive('contains')->with($managedRepair)->once()->andReturn(true);

        self::assertSame(
            [CrewTypeEnum::SCIENCE->value => $detached],
            $this->subject->getSkillEnhancements(SkillEnhancementEnum::MAINTENANCE_TICK)
        );
        self::assertSame(
            [CrewTypeEnum::SCIENCE->value => $managed],
            $this->subject->getSkillEnhancements(SkillEnhancementEnum::MAINTENANCE_TICK)
        );
        self::assertSame(
            [CrewTypeEnum::SCIENCE->value => $managed],
            $this->subject->getSkillEnhancements(SkillEnhancementEnum::MAINTENANCE_TICK)
        );
        self::assertSame(
            [CrewTypeEnum::TECHNICAL->value => $managedRepair],
            $this->subject->getSkillEnhancements(SkillEnhancementEnum::REPAIR_COMPLETED)
        );
    }
}
