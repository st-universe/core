<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftRump;

/**
 * @extends EntityRepository<ShipRumpModuleLevel>
 *
 */
final class ShipRumpModuleLevelRepository extends EntityRepository implements ShipRumpModuleLevelRepositoryInterface
{
    #[\Override]
    public function save(ShipRumpModuleLevel $entity): void
    {
        $em = $this->getEntityManager();

        $em->persist($entity);
    }

    #[\Override]
    public function getByShipRump(SpacecraftRump $rump): ?ShipRumpModuleLevel
    {
        return $this->findOneBy([
            'rump' => $rump
        ]);
    }
}
