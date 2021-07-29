<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipRumpBuildingFunction;

final class ShipBuildplanRepository extends EntityRepository implements ShipBuildplanRepositoryInterface
{
    public function getByUserAndBuildingFunction(int $userId, int $buildingFunction): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT b FROM %s b WHERE b.user_id = :userId AND b.rump_id IN (
                        SELECT bf.rump_id FROM %s bf WHERE bf.building_function = :buildingFunction
                    )',
                    ShipBuildplan::class,
                    ShipRumpBuildingFunction::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'buildingFunction' => $buildingFunction
            ])
            ->getResult();
    }

    public function getCountByRumpAndUser(int $rumpId, int $userId): int
    {
        return $this->count([
            'rump_id' => $rumpId,
            'user_id' => $userId,
        ]);
    }

    public function getByUserShipRumpAndSignature(
        int $userId,
        int $shipRumpId,
        string $signature
    ): ?ShipBuildplanInterface {
        return $this->findOneBy([
            'user_id' => $userId,
            'rump_id' => $shipRumpId,
            'signature' => $signature
        ]);
    }

    public function getShuttleBuildplan(int $commodityId): ?ShipBuildplanInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sb FROM %s sb
                    JOIN %s sr
                    WITH sb.rump_id = sr.id
                    WHERE sr.good_id = :commodityId',
                    ShipBuildplan::class,
                    ShipRump::class
                )
            )
            ->setParameters([
                'commodityId' => $commodityId
            ])
            ->getOneOrNullResult();
    }

    public function getStationBuildplansByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT bp FROM %s bp INDEX BY r.id
                    JOIN %s r WITH bp.rump_id = r.id
                    WHERE r.category_id = :category
                    AND r.id IN (
                        SELECT ru.rump_id FROM %s ru WHERE ru.user_id = :userId
                    )',
                    ShipBuildplan::class,
                    ShipRump::class,
                    ShipRumpUser::class
                )
            )
            ->setParameters([
                'category' => ShipRumpEnum::SHIP_CATEGORY_STATION,
                'userId' => $userId
            ])
            ->getResult();
    }

    public function prototype(): ShipBuildplanInterface
    {
        return new ShipBuildplan();
    }

    public function save(ShipBuildplanInterface $shipBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipBuildplan);
        $em->flush();
    }

    public function delete(ShipBuildplanInterface $shipBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipBuildplan);
        $em->flush();
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }
}
