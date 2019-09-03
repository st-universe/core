<?php

namespace Stu\Orm\Repository;

use Stu\Orm\Entity\ModuleSpecialInterface;

interface ModuleSpecialRepositoryInterface
{
    /**
     * @return ModuleSpecialInterface[]
     */
    public function getByModule(int $moduleId): array;
}