<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\ShipRumpModuleLevelInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

/**
 * @extends EntityRepository<ShipRumpModuleLevel>
 * 
 */
final class ShipRumpModuleLevelRepository extends EntityRepository implements ShipRumpModuleLevelRepositoryInterface
{
    #[Override]
    public function save(ShipRumpModuleLevelInterface $entity): void
    {
        $em = $this->getEntityManager();

        $em->persist($entity);
    }

    #[Override]
    public function getByShipRump(SpacecraftRumpInterface $rump): ?ShipRumpModuleLevelInterface
    {
        return $this->findOneBy([
            'rump' => $rump
        ]);
    }
}
