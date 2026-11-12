<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ModuleCost;

/**
 * @extends EntityRepository<ModuleCost>
 */
final class ModuleCostRepository extends EntityRepository implements ModuleCostRepositoryInterface
{
    #[Override]
    public function save(ModuleCost $cost): void
    {
        $this->getEntityManager()->persist($cost);
    }

    #[Override]
    public function delete(ModuleCost $cost): void
    {
        $this->getEntityManager()->remove($cost);
    }

    #[Override]
    public function getByModule(int $moduleId): array
    {
        return $this->findBy([
            'module_id' => $moduleId
        ]);
    }
}
