<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\WeaponShield;
use Stu\Orm\Entity\WeaponShieldInterface;

/**
 * @extends EntityRepository<WeaponShield>
 */
final class WeaponShieldRepository extends EntityRepository implements WeaponShieldRepositoryInterface
{
    #[Override]
    public function prototype(): WeaponShieldInterface
    {
        return new WeaponShield();
    }

    #[Override]
    public function save(WeaponShieldInterface $weaponshield): void
    {
        $em = $this->getEntityManager();

        $em->persist($weaponshield);
    }

    #[Override]
    public function delete(WeaponShieldInterface $weaponshield): void
    {
        $em = $this->getEntityManager();

        $em->remove($weaponshield);
    }

    #[Override]
    public function getModificatorMinAndMax(): array
    {
        $min =  (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT min(ws.modificator) FROM %s ws',
                WeaponShield::class
            )
        )->getSingleScalarResult();

        $max =  (int)$this->getEntityManager()->createQuery(
            sprintf(
                'SELECT max(ws.modificator) FROM %s ws',
                WeaponShield::class
            )
        )->getSingleScalarResult();

        return [$min, $max];
    }
}
