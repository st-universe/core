<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class MaintenanceCrewExperienceHandler implements MaintenanceHandlerInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    #[\Override]
    public function handle(): void
    {
        foreach ($this->spacecraftRepository->getPlayerSpacecraftsForTick() as $spacecraft) {
            $this->eventDispatcher->dispatch(new CrewExperienceEvent(
                $spacecraft,
                SkillEnhancementEnum::MAINTENANCE_TICK
            ));
        }
    }
}
