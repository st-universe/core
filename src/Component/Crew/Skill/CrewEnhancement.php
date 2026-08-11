<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;

final class CrewEnhancement implements CrewEnhancementInterface
{
    private const int SCIENCE_SCAN_COOLDOWN_IN_SECONDS = 10 * 60 * 60;

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
            if (
                $trigger === SkillEnhancementEnum::FOREIGN_SPACECRAFT_SCAN
                && $this->skillEnhancementLogRepository->hasCrewExperienceSince(
                    $crew,
                    $trigger,
                    time() - self::SCIENCE_SCAN_COOLDOWN_IN_SECONDS
                )
            ) {
                continue;
            }

            $this->raiseExpertise->raiseExpertise(
                $crew,
                $spacecraft,
                $position,
                $enhancements[$position->value],
                $percentage
            );
        }
    }
}
