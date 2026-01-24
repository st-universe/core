<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
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
        $assignments = $this->allianceMemberJobRepository->getByUser($user->getId());

        foreach ($assignments as $assignment) {
            if ($assignment->getJob()->getAlliance()->getId() === $alliance->getId()) {
                return true;
            }
        }

        return false;
    }

    #[\Override]
    public function getUserJobs(User $user, Alliance $alliance): array
    {
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
