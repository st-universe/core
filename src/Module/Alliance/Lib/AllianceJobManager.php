<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Override;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceMemberJobRepositoryInterface;

final class AllianceJobManager implements AllianceJobManagerInterface
{
    public function __construct(
        private AllianceMemberJobRepositoryInterface $allianceMemberJobRepository
    ) {}

    #[Override]
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

    #[Override]
    public function removeUserFromJob(User $user, AllianceJob $job): void
    {
        $assignment = $this->allianceMemberJobRepository->getByUserAndJob($user, $job->getId());
        
        if ($assignment === null) {
            return;
        }

        $this->allianceMemberJobRepository->delete($assignment);
    }

    #[Override]
    public function removeUserFromAllJobs(User $user, Alliance $alliance): void
    {
        $assignments = $this->allianceMemberJobRepository->getByUser($user->getId());
        
        foreach ($assignments as $assignment) {
            if ($assignment->getJob()->getAlliance()->getId() === $alliance->getId()) {
                $this->allianceMemberJobRepository->delete($assignment);
            }
        }
    }

    #[Override]
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

    #[Override]
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

    #[Override]
    public function hasUserFounderPermission(User $user, Alliance $alliance): bool
    {
        $jobs = $this->getUserJobs($user, $alliance);
        
        foreach ($jobs as $job) {
            if ($job->hasFounderPermission()) {
                return true;
            }
        }
        
        return false;
    }

    #[Override]
    public function hasUserSuccessorPermission(User $user, Alliance $alliance): bool
    {
        $jobs = $this->getUserJobs($user, $alliance);
        
        foreach ($jobs as $job) {
            if ($job->hasSuccessorPermission()) {
                return true;
            }
        }
        
        return false;
    }

    #[Override]
    public function hasUserDiplomaticPermission(User $user, Alliance $alliance): bool
    {
        $jobs = $this->getUserJobs($user, $alliance);
        
        foreach ($jobs as $job) {
            if ($job->hasDiplomaticPermission()) {
                return true;
            }
        }
        
        return false;
    }
}
