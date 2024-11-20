<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpUser;

/**
 * @extends EntityRepository<ShipBuildplan>
 */
final class ShipBuildplanRepository extends EntityRepository implements ShipBuildplanRepositoryInterface
{
    #[Override]
    public function getByUserAndBuildingFunction(int $userId, BuildingFunctionEnum $buildingFunction): array
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
                'buildingFunction' => $buildingFunction->value
            ])
            ->getResult();
    }

    #[Override]
    public function getCountByRumpAndUser(int $rumpId, int $userId): int
    {
        return $this->count([
            'rump_id' => $rumpId,
            'user_id' => $userId,
        ]);
    }

    #[Override]
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

    #[Override]
    public function getShuttleBuildplan(int $commodityId): ?ShipBuildplanInterface
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sb FROM %s sb
                    JOIN %s sr
                    WITH sb.rump_id = sr.id
                    WHERE sr.commodity_id = :commodityId',
                    ShipBuildplan::class,
                    ShipRump::class
                )
            )
            ->setParameters([
                'commodityId' => $commodityId
            ])
            ->getOneOrNullResult();
    }

    #[Override]
    public function getStationBuildplansByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT bp FROM %s bp
                    JOIN %s r
                    WITH bp.rump_id = r.id
                    WHERE r.category_id = :category
                    AND r.id IN (
                        SELECT ru.rump_id FROM %s ru WHERE ru.user_id = :userId
                    )
                    ORDER BY r.id ASC',
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

    #[Override]
    public function getShipyardBuildplansByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT bp FROM %s bp
                    JOIN %s r
                    WITH bp.rump_id = r.id
                    WHERE r.category_id != :category
                    AND bp.user_id = :userId
                    ORDER BY r.id ASC',
                    ShipBuildplan::class,
                    ShipRump::class
                )
            )
            ->setParameters([
                'category' => ShipRumpEnum::SHIP_CATEGORY_STATION,
                'userId' => $userId
            ])
            ->getResult();
    }

    #[Override]
    public function prototype(): ShipBuildplanInterface
    {
        return new ShipBuildplan();
    }

    #[Override]
    public function save(ShipBuildplanInterface $shipBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipBuildplan);
    }

    #[Override]
    public function delete(ShipBuildplanInterface $shipBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipBuildplan);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function findByUserAndName(int $userId, string $name): ?ShipBuildplanInterface
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'name' => $name
        ]);
    }

    #[Override]
    public function truncateAllBuildplansExceptNoOne(): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s bp
                    WHERE bp.user_id != :noOne',
                    ShipBuildplan::class
                )
            )
            ->setParameters([
                'noOne' => UserEnum::USER_NOONE
            ])
            ->execute();
    }

    #[Override]
    public function getByUserAndRump(int $userId, int $rumpId): array
    {
        return $this->findBy([
            'user_id' => $userId,
            'rump_id' => $rumpId,
        ]);
    }
}
