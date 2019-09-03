<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class ModuleSpecialRepository extends EntityRepository implements ModuleSpecialRepositoryInterface
{
    public function getByModule(int $moduleId): array
    {
        return $this->findBy([
            'module_id' => $moduleId
        ]);
    }
}