<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;

final class CrewEnhancement implements CrewEnhancementInterface
{
    public function __construct(
        private SkillEnhancementCacheInterface $skillEnhancementCache,
        private RaiseExpertise $raiseExpertise,
        private SkillEnhancementLogRepositoryInterface $skillEnhancementLogRepository
    ) {}

    #[\Override]
    public function addExpertise(
        Spacecraft|SpacecraftWrapperInterface $target,
        SkillEnhancementEnum $trigger,
        int $percentage = 100
    ): void {
        $spacecraft = $target instanceof Spacecraft ? $target : $target->get();
        $enhancements = $this->skillEnhancementCache->getSkillEnhancements($trigger);

        if ($enhancements === null) {
            return;
        }

        foreach ($spacecraft->getCrewAssignments() as $crewAssignment) {
            $position = $crewAssignment->getSlot();
            if ($position === null || !isset($enhancements[$position->value])) {
                continue;
            }

            $crew = $crewAssignment->getCrew();
            $enhancement = $enhancements[$position->value];
            $cooldown = $enhancement->getCooldown();
            if (
                $cooldown !== null
                && $cooldown > 0
                && $this->skillEnhancementLogRepository->hasCrewExperienceSince(
                    $crew,
                    $enhancement,
                    time() - $cooldown
                )
            ) {
                continue;
            }

            $this->raiseExpertise->raiseExpertise(
                $crew,
                $spacecraft,
                $position,
                $enhancement,
                $percentage
            );
        }
    }
}
