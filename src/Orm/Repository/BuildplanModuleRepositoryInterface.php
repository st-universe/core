<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\BuildplanModuleInterface;

/**
 * @extends ObjectRepository<BuildplanModule>
 */
interface BuildplanModuleRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<BuildplanModuleInterface>
     */
    public function getByBuildplan(int $buildplanId): array;

    /**
     * @return array<BuildplanModuleInterface>
     */
    public function getByBuildplanAndModuleType(int $buildplanId, int $moduleType): array;

    public function prototype(): BuildplanModuleInterface;

    public function save(BuildplanModuleInterface $obj): void;

    public function delete(BuildplanModuleInterface $obj): void;

    public function truncateByBuildplan(int $buildplanId): void;
}
