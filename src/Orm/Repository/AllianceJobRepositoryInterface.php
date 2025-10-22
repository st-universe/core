<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<AllianceJob>
 *
 * @method null|AllianceJob find(integer $id)
 */
interface AllianceJobRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceJob;

    public function save(AllianceJob $post): void;

    public function delete(AllianceJob $post): void;

    /**
     * @return array<AllianceJob>
     */
    public function getByAlliance(int $allianceId): array;

    public function truncateByAlliance(int $allianceId): void;

    /**
     * @return array<AllianceJob>
     */
    public function getJobsWithFounderPermission(int $allianceId): array;

    /**
     * @return array<AllianceJob>
     */
    public function getJobsWithSuccessorPermission(int $allianceId): array;

    /**
     * @return array<AllianceJob>
     */
    public function getJobsWithDiplomaticPermission(int $allianceId): array;

    public function getByAllianceAndTitle(int $allianceId, string $title): ?AllianceJob;
}
