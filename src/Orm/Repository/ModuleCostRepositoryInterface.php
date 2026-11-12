<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ModuleCost;

/**
 * @extends ObjectRepository<ModuleCost>
 */
interface ModuleCostRepositoryInterface extends ObjectRepository
{
    public function save(ModuleCost $cost): void;

    public function delete(ModuleCost $cost): void;

    /**
     * @return list<ModuleCost>
     */
    public function getByModule(int $moduleId): array;
}
