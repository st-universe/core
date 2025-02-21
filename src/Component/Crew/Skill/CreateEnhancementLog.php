<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\CrewSkillInterface;
use Stu\Orm\Entity\SkillEnhancementInterface;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;

class CreateEnhancementLog
{
    public function __construct(
        private SkillEnhancementLogRepositoryInterface $skillEnhancementLogRepository,
        private StuTime $stuTime
    ) {}

    public function createEnhancementLog(
        CrewSkillInterface $crewSkill,
        string $spacecraftName,
        SkillEnhancementInterface $enhancement,
        int $amount,
        CrewSkillLevelEnum $oldRank
    ): void {

        $crew = $crewSkill->getCrew();

        $log = $this->skillEnhancementLogRepository
            ->prototype()
            ->setUser($crew->getUser())
            ->setEnhancement($enhancement)
            ->setCrewName($crew->getName())
            ->setShipName($spacecraftName)
            ->setCrewId($crew->getId())
            ->setExpertise($amount)
            ->setExpertiseSum($crewSkill->getExpertise())
            ->setPromotion($this->getPromotion($oldRank, $crewSkill->getRank()))
            ->setTimestamp($this->stuTime->time());

        $this->skillEnhancementLogRepository->save($log);
    }

    private function getPromotion(CrewSkillLevelEnum $oldRank, CrewSkillLevelEnum $newRank): ?string
    {
        return $oldRank !== $newRank
            ? sprintf('Beförderung %s -> %s', $oldRank->getDescription(), $newRank->getDescription())
            : null;
    }
}
