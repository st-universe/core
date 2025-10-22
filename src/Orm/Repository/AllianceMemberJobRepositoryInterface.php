<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceMemberJob;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<AllianceMemberJob>
 */
interface AllianceMemberJobRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceMemberJob;

    public function save(AllianceMemberJob $allianceMemberJob): void;

    public function delete(AllianceMemberJob $allianceMemberJob): void;

    /**
     * @return array<AllianceMemberJob>
     */
    public function getByUser(int $userId): array;

    /**
     * @return array<AllianceMemberJob>
     */
    public function getByJob(int $jobId): array;

    public function truncateByUser(int $userId): void;

    public function truncateByJob(int $jobId): void;

    public function getByUserAndJob(User $user, int $jobId): ?AllianceMemberJob;
}
