<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpUser;
use Stu\Orm\Entity\SpacecraftRump;

/**
 * @extends EntityRepository<SpacecraftBuildplan>
 */
final class SpacecraftBuildplanRepository extends EntityRepository implements SpacecraftBuildplanRepositoryInterface
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
                    SpacecraftBuildplan::class,
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
        int $rumpId,
        string $signature
    ): ?SpacecraftBuildplan {
        return $this->findOneBy([
            'user_id' => $userId,
            'rump_id' => $rumpId,
            'signature' => $signature
        ]);
    }

    #[Override]
    public function getShuttleBuildplan(int $commodityId): ?SpacecraftBuildplan
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sb FROM %s sb
                    JOIN %s sr
                    WITH sb.rump_id = sr.id
                    WHERE sr.commodity_id = :commodityId',
                    SpacecraftBuildplan::class,
                    SpacecraftRump::class
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
                    SpacecraftBuildplan::class,
                    SpacecraftRump::class,
                    ShipRumpUser::class
                )
            )
            ->setParameters([
                'category' => SpacecraftRumpCategoryEnum::STATION->value,
                'userId' => $userId
            ])
            ->getResult();
    }

    #[Override]
    public function getStationBuildplanByRump(int $rumpId): ?SpacecraftBuildplan
    {
        return $this->findOneBy([
            'rump_id' => $rumpId
        ]);
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
                    SpacecraftBuildplan::class,
                    SpacecraftRump::class
                )
            )
            ->setParameters([
                'category' => SpacecraftRumpCategoryEnum::STATION->value,
                'userId' => $userId
            ])
            ->getResult();
    }

    #[Override]
    public function prototype(): SpacecraftBuildplan
    {
        return new SpacecraftBuildplan();
    }

    #[Override]
    public function save(SpacecraftBuildplan $spacecraftBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->persist($spacecraftBuildplan);
    }

    #[Override]
    public function delete(SpacecraftBuildplan $spacecraftBuildplan): void
    {
        $em = $this->getEntityManager();

        $em->remove($spacecraftBuildplan);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function findByUserAndName(int $userId, string $name): ?SpacecraftBuildplan
    {
        return $this->findOneBy([
            'user_id' => $userId,
            'name' => $name
        ]);
    }

    public function getAllNonNpcBuildplans(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT bp FROM %s bp
                    WHERE bp.user_id >= :firstUserId',
                    SpacecraftBuildplan::class
                )
            )
            ->setParameter('firstUserId', UserConstants::USER_FIRST_ID)
            ->getResult();
    }

    #[Override]
    public function truncateAllBuildplansExceptNoOne(): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s bp
                    WHERE bp.user_id != :noOne',
                    SpacecraftBuildplan::class
                )
            )
            ->setParameters([
                'noOne' => UserConstants::USER_NOONE
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
