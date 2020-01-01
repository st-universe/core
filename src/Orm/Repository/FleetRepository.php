<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

final class FleetRepository extends EntityRepository implements FleetRepositoryInterface
{
    public function prototype(): FleetInterface
    {
        return new Fleet();
    }

    public function save(FleetInterface $fleet): void
    {
        $em = $this->getEntityManager();

        $em->persist($fleet);
        $em->flush();
    }

    public function delete(FleetInterface $fleet): void
    {
        $em = $this->getEntityManager();

        $em->remove($fleet);
        $em->flush();
    }

    public function truncateByUser(UserInterface $user): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s f WHERE f.user_id = :user',
                Fleet::class
            )
        )
            ->setParameters(['user' => $user])
            ->execute();
    }

    public function getByUser(int $userId): iterable
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['id' => 'desc']
        );
    }

    public function getByPositition(
        ?StarSystemInterface $starSystem,
        int $cx,
        int $cy,
        int $sx,
        int $sy
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT f FROM %s f LEFT JOIN %s s WITH s.id = f.ships_id WHERE
                (s.starSystem = :starSystem OR (s.starSystem IS NULL AND :starSystem is NULL))
                AND s.cx = :cx AND s.cy = :cy AND s.sx = :sx AND s.sy = :sy AND s.is_base = :isBase',
                Fleet::class,
                Ship::class
            )
        )
            ->setParameters([
                'isBase' => 0,
                'starSystem' => $starSystem,
                'cx' => $cx,
                'cy' => $cy,
                'sx' => $sx,
                'sy' => $sy
            ])
            ->getResult();
    }
}
