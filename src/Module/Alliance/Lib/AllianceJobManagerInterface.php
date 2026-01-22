<?php

namespace Stu\Module\Alliance\Lib;

use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;

interface AllianceJobManagerInterface
{
    public function assignUserToJob(User $user, AllianceJob $job): void;

    public function removeUserFromJob(User $user, AllianceJob $job): void;

    public function removeUserFromAllJobs(User $user, Alliance $alliance): void;

    public function hasUserJob(User $user, Alliance $alliance): bool;

    /**
     * @return array<AllianceJob>
     */
    public function getUserJobs(User $user, Alliance $alliance): array;

    public function hasUserFounderPermission(User $user, Alliance $alliance): bool;

    public function hasUserSuccessorPermission(User $user, Alliance $alliance): bool;

    public function hasUserDiplomaticPermission(User $user, Alliance $alliance): bool;

    public function hasUserPermission(User $user, Alliance $alliance, int $permissionType): bool;
}
