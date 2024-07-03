<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpUser;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<ShipRump>
 */
final class ShipRumpRepository extends EntityRepository implements ShipRumpRepositoryInterface
{
    #[Override]
    public function getGroupedInfoByUser(UserInterface $user): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s.rumps_id as rump_id, r.name, COUNT(s.id) as amount FROM %s s LEFT JOIN %s r WITH
                    r.id = s.rumps_id WHERE s.user = :user GROUP BY s.rumps_id, r.name ORDER BY MIN(r.sort) ASC',
                    Ship::class,
                    ShipRump::class
                )
            )
            ->setParameters([
                'user' => $user
            ])
            ->getResult();
    }

    #[Override]
    public function getBuildableByUserAndBuildingFunction(int $userId, int $buildingFunction): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r INDEX BY r.id WHERE r.is_buildable = :state AND r.id IN (
                        SELECT ru.rump_id FROM %s ru WHERE ru.user_id = :userId
                    ) AND r.id IN (
                        SELECT rubu.rump_id FROM %s rubu WHERE rubu.building_function = :buildingFunction
                    )',
                    ShipRump::class,
                    ShipRumpUser::class,
                    ShipRumpBuildingFunction::class
                )
            )
            ->setParameters([
                'state' => 1,
                'userId' => $userId,
                'buildingFunction' => $buildingFunction
            ])
            ->getResult();
    }

    #[Override]
    public function getBuildableByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r INDEX BY r.id WHERE r.is_buildable = :state AND r.id IN (
                        SELECT ru.rump_id FROM %s ru WHERE ru.user_id = :userId
                    )',
                    ShipRump::class,
                    ShipRumpUser::class
                )
            )
            ->setParameters([
                'state' => 1,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[Override]
    public function getWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r WHERE r.database_id NOT IN (SELECT d.id FROM %s d WHERE d.type = :categoryId)',
                    ShipRump::class,
                    DatabaseEntry::class
                )
            )
            ->setParameters([
                'categoryId' => DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP
            ])
            ->getResult();
    }

    #[Override]
    public function getStartableByColony(int $colonyId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r INDEX BY r.id
                    WHERE r.is_buildable = :state
                    AND r.category_id != :ignoreCategory AND r.commodity_id IN (
                        SELECT st.commodity_id FROM %s st WHERE st.colony_id = :colonyId
                    )',
                    ShipRump::class,
                    Storage::class
                )
            )
            ->setParameters([
                'state' => 1,
                'ignoreCategory' => ShipRumpEnum::SHIP_CATEGORY_SHUTTLE,
                'colonyId' => $colonyId
            ])
            ->getResult();
    }

    #[Override]
    public function getList(): iterable
    {
        return $this->findBy(
            [],
            ['id' => 'desc']
        );
    }
}
