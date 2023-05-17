<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\WeaponShieldInterface;
use Stu\Orm\Entity\WeaponShield;

/**
 * @extends EntityRepository<WeaponShield>
 */
final class WeaponShieldRepository extends EntityRepository implements WeaponShieldRepositoryInterface
{
    public function prototype(): WeaponShieldInterface
    {
        return new WeaponShield();
    }

    public function save(WeaponShieldInterface $weaponshield): void
    {
        $em = $this->getEntityManager();

        $em->persist($weaponshield);
    }

    public function delete(WeaponShieldInterface $weaponshield): void
    {
        $em = $this->getEntityManager();

        $em->remove($weaponshield);
    }

    public function getModificatorMinAndMax(): array
    {
        $min =  $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT min(ws.modificator) FROM %s ws',
                WeaponShield::class
            )
        )->getSingleScalarResult();

        $max =  $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT max(ws.modificator) FROM %s ws',
                WeaponShield::class
            )
        )->getSingleScalarResult();

        return [$min, $max];
    }
}
