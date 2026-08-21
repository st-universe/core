<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Mockery;
use Mockery\MockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class MaintenanceCrewExperienceHandlerTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;

    private MockInterface&EventDispatcherInterface $eventDispatcher;

    private MaintenanceCrewExperienceHandler $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->eventDispatcher = $this->mock(EventDispatcherInterface::class);

        $this->subject = new MaintenanceCrewExperienceHandler(
            $this->spacecraftRepository,
            $this->eventDispatcher
        );
    }

    public function testHandleDispatchesExperienceEventForEveryPlayerSpacecraft(): void
    {
        $firstSpacecraft = $this->mock(Spacecraft::class);
        $secondSpacecraft = $this->mock(Spacecraft::class);

        $this->spacecraftRepository->shouldReceive('getPlayerSpacecraftsForTick')
            ->withNoArgs()
            ->once()
            ->andReturn([$firstSpacecraft, $secondSpacecraft]);

        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(Mockery::on(
                fn (CrewExperienceEvent $event): bool => $event->getSpacecraft() === $firstSpacecraft
                    && $event->getTrigger() === SkillEnhancementEnum::MAINTENANCE_TICK
            ))
            ->once()
            ->ordered();
        $this->eventDispatcher->shouldReceive('dispatch')
            ->with(Mockery::on(
                fn (CrewExperienceEvent $event): bool => $event->getSpacecraft() === $secondSpacecraft
                    && $event->getTrigger() === SkillEnhancementEnum::MAINTENANCE_TICK
            ))
            ->once()
            ->ordered();

        $this->subject->handle();
    }
}
