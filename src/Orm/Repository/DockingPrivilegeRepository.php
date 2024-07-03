<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\DockingPrivilegeInterface;

/**
 * @extends EntityRepository<DockingPrivilege>
 */
final class DockingPrivilegeRepository extends EntityRepository implements DockingPrivilegeRepositoryInterface
{
    #[Override]
    public function prototype(): DockingPrivilegeInterface
    {
        return new DockingPrivilege();
    }

    #[Override]
    public function save(DockingPrivilegeInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(DockingPrivilegeInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function existsForTargetAndTypeAndShip(int $targetId, int $privilegeType, int $shipId): bool
    {
        return $this->count([
            'ships_id' => $shipId,
            'target' => $targetId,
            'privilege_type' => $privilegeType,
        ]) > 0;
    }

    #[Override]
    public function getByShip(int $shipId): array
    {
        return $this->findBy([
            'ships_id' => $shipId,
        ]);
    }

    #[Override]
    public function truncateByTypeAndTarget(int $typeId, int $targetId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s dp WHERE dp.target = :targetId AND dp.privilege_type = :typeId',
                    DockingPrivilege::class
                )
            )
            ->setParameters([
                'typeId' => $typeId,
                'targetId' => $targetId,
            ])
            ->execute();
    }
}
