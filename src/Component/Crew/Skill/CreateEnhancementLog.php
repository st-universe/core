<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\CrewSkill;
use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class CreateEnhancementLog
{
    public function __construct(
        private SkillEnhancementLogRepositoryInterface $skillEnhancementLogRepository,
        private StuTime $stuTime,
        private UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    public function createEnhancementLog(
        CrewSkill $crewSkill,
        string $spacecraftName,
        SkillEnhancement $enhancement,
        int $amount,
        CrewSkillLevelEnum $oldRank,
        CrewSkillLevelEnum $newRank
    ): void {
        $crew = $crewSkill->getCrew();
        $log = $this->skillEnhancementLogRepository->prototype()
            ->setUser($crew->getUser())
            ->setEnhancement($enhancement)
            ->setCrewName($crew->getName())
            ->setShipName($spacecraftName)
            ->setCrewId($crew->getId())
            ->setExpertise($amount)
            ->setExpertiseSum($crewSkill->getExpertise())
            ->setPromotion($this->getPromotion($oldRank, $newRank, $crew->getUser()))
            ->setTimestamp($this->stuTime->time());

        $this->skillEnhancementLogRepository->save($log);
    }

    private function getPromotion(
        CrewSkillLevelEnum $oldRank,
        CrewSkillLevelEnum $newRank,
        User $user
    ): ?string {
        return $oldRank === $newRank
            ? null
            : sprintf(
                'Befoerderung %s -> %s',
                $this->userCrewRankRepository->getRankName($user, $oldRank),
                $this->userCrewRankRepository->getRankName($user, $newRank)
            );
    }
}
