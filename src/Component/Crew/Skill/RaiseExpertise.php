<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewSkillRepositoryInterface;

final class RaiseExpertise
{
    public function __construct(
        private CrewSkillRepositoryInterface $crewSkillRepository,
        private CrewRepositoryInterface $crewRepository,
        private CreateEnhancementLog $createEnhancementLog
    ) {}

    public function raiseExpertise(
        Crew $crew,
        Spacecraft $spacecraft,
        CrewTypeEnum $position,
        SkillEnhancement $enhancement,
        int $percentage
    ): void {
        $skill = $crew->getSkills()->get($position->value);
        if ($skill === null) {
            $skill = $this->crewSkillRepository->prototype()
                ->setCrew($crew)
                ->setPosition($position);
            $crew->getSkills()->set($position->value, $skill);
        }

        $oldCrewRank = $crew->getRank();
        $amount = (int)ceil($enhancement->getExpertise() * max(0, $percentage) / 100);
        $skill->increaseExpertise($amount);
        $this->crewSkillRepository->save($skill);

        $newCrewRank = $skill->getRank()->getAutomaticPromotionTarget();
        if ($newCrewRank->getNeededExpertise() > $oldCrewRank->getNeededExpertise()) {
            $crew->setRank($newCrewRank);
            $this->crewRepository->save($crew);
        } else {
            $newCrewRank = $oldCrewRank;
        }

        $this->createEnhancementLog->createEnhancementLog(
            $skill,
            $spacecraft->getName(),
            $enhancement,
            $amount,
            $oldCrewRank,
            $newCrewRank
        );
    }
}
