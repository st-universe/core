<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceJob;

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
    public function getJobsWithPermission(int $allianceId, int $permissionType): array;

    public function getByAllianceAndTitle(int $allianceId, string $title): ?AllianceJob;
}
