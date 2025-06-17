<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<ShipTakeover>
 */
final class ShipTakeoverRepository extends EntityRepository implements ShipTakeoverRepositoryInterface
{
    #[Override]
    public function prototype(): ShipTakeoverInterface
    {
        return new ShipTakeover();
    }

    #[Override]
    public function save(ShipTakeoverInterface $shipTakeover): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipTakeover);
    }

    #[Override]
    public function delete(ShipTakeoverInterface $shipTakeover): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipTakeover);
    }

    #[Override]
    public function getByTargetOwner(UserInterface $user): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT sto FROM %s sto
                JOIN %s sp
                WITH sto.target = sp
                WHERE sp.user = :user',
                ShipTakeover::class,
                Spacecraft::class
            )
        )
            ->setParameter('user', $user)
            ->getResult();
    }
}
