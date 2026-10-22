<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;

final class AllianceJobManager implements AllianceJobManagerInterface
{
    public function __construct(
        private AllianceMemberJobRepositoryInterface $allianceMemberJobRepository
    ) {}

    #[\Override]
    public function assertUserCanManageMember(User $actor, User $target, Alliance $alliance): void
    {
        if ($actor->getAlliance()?->getId() !== $alliance->getId()
            || $target->getAlliance()?->getId() !== $alliance->getId()
            || !$this->hasUserPermission($actor, $alliance, AllianceJobPermissionEnum::MANAGE_JOBS)
            || $this->hasUserPermission($target, $alliance, AllianceJobPermissionEnum::FOUNDER)) {
            throw new AccessViolationException();
        }
    }

    #[\Override]
    public function joinAlliance(User $user, Alliance $alliance): void
    {
        if ($user->getAlliance() !== null) {
            throw new AccessViolationException();
        }

        $this->allianceMemberJobRepository->truncateByUser($user->getId());
        $user->setAlliance($alliance);
    }

    #[\Override]
    public function assignUserToJob(User $user, AllianceJob $job): void
    {
        $existing = $this->allianceMemberJobRepository->getByUserAndJob($user, $job->getId());

        if ($existing !== null) {
            return;
        }

        $assignment = $this->allianceMemberJobRepository->prototype();
        $assignment->setUser($user);
        $assignment->setJob($job);

        $this->allianceMemberJobRepository->save($assignment);
    }

    #[\Override]
    public function removeUserFromJob(User $user, AllianceJob $job): void
    {
        $assignment = $this->allianceMemberJobRepository->getByUserAndJob($user, $job->getId());

        if ($assignment === null) {
            return;
        }

        $this->allianceMemberJobRepository->delete($assignment);
    }

    #[\Override]
    public function removeUserFromAllJobs(User $user, Alliance $alliance): void
    {
        $assignments = $this->allianceMemberJobRepository->getByUser($user->getId());

        foreach ($assignments as $assignment) {
            if ($assignment->getJob()->getAlliance()->getId() === $alliance->getId()) {
                $this->allianceMemberJobRepository->delete($assignment);
            }
        }
    }

    #[\Override]
    public function hasUserJob(User $user, Alliance $alliance): bool
    {
        return $this->getUserJobs($user, $alliance) !== [];
    }

    #[\Override]
    public function getUserJobs(User $user, Alliance $alliance): array
    {
        if ($user->getAlliance()?->getId() !== $alliance->getId()) {
            return [];
        }

        $assignments = $this->allianceMemberJobRepository->getByUser($user->getId());
        $jobs = [];

        foreach ($assignments as $assignment) {
            if ($assignment->getJob()->getAlliance()->getId() === $alliance->getId()) {
                $jobs[] = $assignment->getJob();
            }
        }

        return $jobs;
    }

    #[\Override]
    public function hasUserPermission(User $user, Alliance $alliance, AllianceJobPermissionEnum $permissionType): bool
    {
        $jobs = $this->getUserJobs($user, $alliance);

        foreach ($jobs as $job) {
            if ($job->hasPermission(AllianceJobPermissionEnum::FOUNDER->value)) {
                return true;
            }

            if ($job->hasPermission($permissionType->value)) {
                return true;
            }

            $parentPermission = $permissionType->getParentPermission();
            if ($parentPermission !== null && $job->hasPermission($parentPermission->value)) {
                return true;
            }
        }

        return false;
    }
}
