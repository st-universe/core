<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ModuleSpecial;

/**
 * @extends EntityRepository<ModuleSpecial>
 */
final class ModuleSpecialRepository extends EntityRepository implements ModuleSpecialRepositoryInterface
{
    #[\Override]
    public function getByModule(int $moduleId): array
    {
        return $this->findBy([
            'module_id' => $moduleId
        ]);
    }
}
