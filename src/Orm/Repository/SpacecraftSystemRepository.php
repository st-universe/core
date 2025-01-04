<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\SpacecraftSystemInterface;

/**
 * @extends EntityRepository<SpacecraftSystem>
 */
final class SpacecraftSystemRepository extends EntityRepository implements SpacecraftSystemRepositoryInterface
{
    #[Override]
    public function prototype(): SpacecraftSystemInterface
    {
        return new SpacecraftSystem();
    }

    #[Override]
    public function save(SpacecraftSystemInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(SpacecraftSystemInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getByShip(int $shipId): array
    {
        return $this->findBy(
            ['spacecraft_id' => $shipId],
            ['system_type' => 'asc']
        );
    }

    #[Override]
    public function getTrackingShipSystems(int $targetId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ss FROM %s ss
                    WHERE ss.system_type = :systemType
                    AND ss.data LIKE :target',
                    SpacecraftSystem::class
                )
            )
            ->setParameters([
                'systemType' => SpacecraftSystemTypeEnum::TRACKER,
                'target' => sprintf('%%"targetId":%d%%', $targetId)
            ])
            ->getResult();
    }

    #[Override]
    public function getWebConstructingShipSystems(int $webId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ss FROM %s ss
                    WHERE ss.system_type = :systemType
                    AND ss.data LIKE :target',
                    SpacecraftSystem::class
                )
            )
            ->setParameters([
                'systemType' => SpacecraftSystemTypeEnum::THOLIAN_WEB,
                'target' => sprintf('%%"webUnderConstructionId":%d%%', $webId)
            ])
            ->getResult();
    }

    #[Override]
    public function getWebOwningShipSystem(int $webId): ?SpacecraftSystemInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT ss FROM %s ss
                    WHERE ss.system_type = :systemType
                    AND ss.data LIKE :target',
                    SpacecraftSystem::class
                )
            )
            ->setParameters([
                'systemType' => SpacecraftSystemTypeEnum::THOLIAN_WEB,
                'target' => sprintf('%%"ownedWebId":%d%%', $webId)
            ])
            ->getOneOrNullResult();
    }

    #[Override]
    public function truncateByShip(int $shipId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s s WHERE s.spacecraft_id = :shipId',
                    SpacecraftSystem::class
                )
            )
            ->setParameter('shipId', $shipId)
            ->execute();
    }
}
