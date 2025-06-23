<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Component\Crew\CrewPositionEnum;
use Stu\Orm\Entity\CrewInterface;
use Stu\Orm\Entity\CrewSkillInterface;
use Stu\Orm\Entity\SkillEnhancementInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\CrewSkillRepositoryInterface;

class RaiseExpertise
{
    public function __construct(
        private CrewSkillRepositoryInterface $crewSkillRepository,
        private CrewRepositoryInterface $crewRepository,
        private CreateEnhancementLog $createEnhancementLog
    ) {}

    public function raiseExpertise(
        CrewInterface $crew,
        SpacecraftInterface $spacecraft,
        CrewPositionEnum $position,
        SkillEnhancementInterface $enhancement,
        int $percentage
    ): void {
        $skills = $crew->getSkills();

        /** @var null|CrewSkillInterface */
        $skill = $skills->get($position->value);
        if ($skill === null) {
            $skill = $this->crewSkillRepository
                ->prototype()
                ->setCrew($crew)
                ->setPosition($position);

            $skills->set($position->value, $skill);
        }

        $oldRank = $skill->getRank();
        $amount = (int)ceil($enhancement->getExpertise() * $percentage / 100);
        $skill->increaseExpertise($amount);
        $this->crewSkillRepository->save($skill);

        if ($skill->getRank()->getNeededExpertise() > $oldRank->getNeededExpertise()) {
            $crew->setRank($skill->getRank());
            $this->crewRepository->save($crew);
        }

        $this->createEnhancementLog->createEnhancementLog(
            $skill,
            $spacecraft->getName(),
            $enhancement,
            $amount,
            $oldRank
        );
    }
}
