<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Database\DatabaseEntryTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\DatabaseEntry;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\ShipRumpBuildingFunction;
use Stu\Orm\Entity\ShipRumpUser;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<SpacecraftRump>
 */
final class SpacecraftRumpRepository extends EntityRepository implements SpacecraftRumpRepositoryInterface
{
    #[\Override]
    public function save(SpacecraftRump $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function getGroupedInfoByUser(User $user): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s.rump_id as rump_id, r.name, COUNT(s.id) as amount FROM %s s LEFT JOIN %s r WITH
                    r.id = s.rump_id WHERE s.user = :user GROUP BY s.rump_id, r.name ORDER BY MIN(r.sort) ASC',
                    Spacecraft::class,
                    SpacecraftRump::class
                )
            )
            ->setParameters([
                'user' => $user
            ])
            ->getResult();
    }

    #[\Override]
    public function getBuildableByUserAndBuildingFunction(int $userId, BuildingFunctionEnum $buildingFunction): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r INDEX BY r.id WHERE r.is_buildable = :state AND r.id IN (
                        SELECT ru.rump_id FROM %s ru WHERE ru.user_id = :userId
                    ) AND r.id IN (
                        SELECT rubu.rump_id FROM %s rubu WHERE rubu.building_function = :buildingFunction
                    )',
                    SpacecraftRump::class,
                    ShipRumpUser::class,
                    ShipRumpBuildingFunction::class
                )
            )
            ->setParameters([
                'state' => 1,
                'userId' => $userId,
                'buildingFunction' => $buildingFunction->value
            ])
            ->getResult();
    }

    #[\Override]
    public function getBuildableByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r INDEX BY r.id WHERE r.is_buildable = :state AND r.id IN (
                        SELECT ru.rump_id FROM %s ru WHERE ru.user_id = :userId
                    )',
                    SpacecraftRump::class,
                    ShipRumpUser::class
                )
            )
            ->setParameters([
                'state' => 1,
                'userId' => $userId,
            ])
            ->getResult();
    }

    #[\Override]
    public function getWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT r FROM %s r WHERE r.database_id NOT IN (SELECT d.id FROM %s d WHERE d.type = :categoryId)',
                    SpacecraftRump::class,
                    DatabaseEntry::class
                )
            )
            ->setParameters([
                'categoryId' => DatabaseEntryTypeEnum::DATABASE_TYPE_RUMP
            ])
            ->getResult();
    }

    #[\Override]
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
                    SpacecraftRump::class,
                    Storage::class
                )
            )
            ->setParameters([
                'state' => 1,
                'ignoreCategory' => SpacecraftRumpCategoryEnum::SHUTTLE->value,
                'colonyId' => $colonyId
            ])
            ->getResult();
    }

    #[\Override]
    public function getList(): array
    {
        return $this->findBy(
            [],
            ['id' => 'desc']
        );
    }
}
