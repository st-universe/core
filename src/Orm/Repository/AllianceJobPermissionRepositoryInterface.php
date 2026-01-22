<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\AllianceJobPermission;

/**
 * @extends ObjectRepository<AllianceJobPermission>
 *
 * @method null|AllianceJobPermission find(integer $id)
 */
interface AllianceJobPermissionRepositoryInterface extends ObjectRepository
{
    public function prototype(): AllianceJobPermission;

    public function save(AllianceJobPermission $permission): void;

    public function delete(AllianceJobPermission $permission): void;

    /**
     * @return array<AllianceJobPermission>
     */
    public function getByJob(int $jobId): array;

    public function deleteByJob(int $jobId): void;
}
