<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

class CrewEnhancement implements CrewEnhancementInterface
{
    public function __construct(
        private SkillEnhancementCacheInterface $skillEnhancementCache,
        private RaiseExpertise $raiseExpertise
    ) {}

    public function addExpertise(
        SpacecraftInterface|SpacecraftWrapperInterface $target,
        SkillEnhancementEnum $trigger,
        int $percentage
    ): void {

        $spacecraft = $target instanceof SpacecraftInterface ? $target : $target->get();

        $enhancements = $this->skillEnhancementCache->getSkillEnhancements($trigger);
        if ($enhancements === null) {
            return;
        }

        foreach ($spacecraft->getCrewAssignments() as $crewAssignment) {
            $position = $crewAssignment->getPosition();
            if ($position === null) {
                continue;
            }

            if (!array_key_exists($position->value, $enhancements)) {
                continue;
            }

            $this->raiseExpertise->raiseExpertise(
                $crewAssignment->getCrew(),
                $spacecraft,
                $position,
                $enhancements[$position->value],
                $percentage
            );
        }
    }
}
