<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\FleetInterface;

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
        $em->flush($fleet);
    }

    public function delete(FleetInterface $fleet): void
    {
        $em = $this->getEntityManager();

        $em->remove($fleet);
        $em->flush($fleet);
    }

    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s f WHERE f.user_id = :userId',
                Fleet::class
            )
        )
            ->setParameters(['userId' => $userId])
            ->execute();
    }

    public function getByUser(int $userId): iterable
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['id' => 'desc']
        );
    }
}