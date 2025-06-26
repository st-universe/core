<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildplanModule;

/**
 * @extends ObjectRepository<BuildplanModule>
 */
interface BuildplanModuleRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<BuildplanModule>
     */
    public function getByBuildplan(int $buildplanId): array;

    public function prototype(): BuildplanModule;

    public function save(BuildplanModule $obj): void;

    public function delete(BuildplanModule $obj): void;

    public function truncateByBuildplan(int $buildplanId): void;
}
