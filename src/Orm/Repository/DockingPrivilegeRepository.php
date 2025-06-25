<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Station\Dock\DockTypeEnum;
use Stu\Orm\Entity\DockingPrivilege;
use Stu\Orm\Entity\DockingPrivilegeInterface;
use Stu\Orm\Entity\StationInterface;

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
    public function existsForTargetAndTypeAndShip(int $targetId, DockTypeEnum $privilegeType, StationInterface $station): bool
    {
        return $this->count([
            'station' => $station,
            'target' => $targetId,
            'privilege_type' => $privilegeType->value,
        ]) > 0;
    }

    #[Override]
    public function getByStation(StationInterface $station): array
    {
        return $this->findBy(
            ['station' => $station],
            ['privilege_mode' => 'DESC']
        );
    }

    #[Override]
    public function truncateByTypeAndTarget(DockTypeEnum $type, int $targetId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s dp WHERE dp.target = :targetId AND dp.privilege_type = :typeId',
                    DockingPrivilege::class
                )
            )
            ->setParameters([
                'typeId' => $type->value,
                'targetId' => $targetId,
            ])
            ->execute();
    }
}
