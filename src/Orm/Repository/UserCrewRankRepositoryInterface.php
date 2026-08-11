<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserCrewRank;

/**
 * @extends ObjectRepository<UserCrewRank>
 */
interface UserCrewRankRepositoryInterface extends ObjectRepository
{
    public function prototype(): UserCrewRank;

    public function save(UserCrewRank $userCrewRank): void;

    public function delete(UserCrewRank $userCrewRank): void;

    public function getByUserAndRank(User $user, CrewSkillLevelEnum $rank): ?UserCrewRank;

    public function getRankName(User $user, CrewSkillLevelEnum $rank): string;

    public function getCustomRankName(User $user, CrewSkillLevelEnum $rank): string;

    public function truncateByUser(User $user): void;
}
