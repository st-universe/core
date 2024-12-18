<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Spacecraft>
 */
final class SpacecraftRepository extends EntityRepository implements SpacecraftRepositoryInterface
{
    #[Override]
    public function save(SpacecraftInterface $spacecraft): void
    {
        $em = $this->getEntityManager();

        $em->persist($spacecraft);
    }

    #[Override]
    public function delete(SpacecraftInterface $spacecraft): void
    {
        $em = $this->getEntityManager();

        $em->remove($spacecraft);
    }

    #[Override]
    public function getAmountByUserAndSpecialAbility(
        int $userId,
        int $specialAbilityId
    ): int {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(s)
                FROM %s s
                JOIN %s bp
                WITH s.plan_id = bp.id
                WHERE s.user_id = :userId AND s.rump_id IN (
                    SELECT rs.rump_id FROM %s rs WHERE rs.special = :specialAbilityId
                )
                %s',
                Spacecraft::class,
                SpacecraftBuildplan::class,
                ShipRumpSpecial::class,
                $specialAbilityId === ShipRumpSpecialAbilityEnum::COLONIZE ? 'AND bp.crew = 0' : ''
            )
        )->setParameters([
            'userId' => $userId,
            'specialAbilityId' => $specialAbilityId,
        ])->getSingleScalarResult();
    }

    #[Override]
    public function getAmountByUserAndRump(int $userId, int $rumpId): int
    {
        return $this->count([
            'user_id' => $userId,
            'rump_id' => $rumpId,
        ]);
    }

    #[Override]
    public function getByUser(UserInterface $user): array
    {
        return $this->findBy([
            'user_id' => $user,
        ]);
    }

    #[Override]
    public function getByLocation(LocationInterface $location): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sc FROM %s sc
                    LEFT JOIN %s s
                    WITH sc.id = s.id
                    LEFT JOIN %s f
                    WITH s.fleet_id = f.id
                    JOIN %s r
                    WITH sc.rump_id = r.id
                    WHERE sc.location = :location
                    AND NOT EXISTS (SELECT ss.id
                                        FROM %s ss
                                        WHERE sc.id = ss.spacecraft_id
                                        AND ss.system_type = :systemId
                                        AND ss.mode > 1)
                    ORDER BY f.sort DESC, f.id DESC, s.is_fleet_leader DESC,
                    r.category_id ASC, r.role_id ASC, r.id ASC, sc.name ASC',
                    Spacecraft::class,
                    Ship::class,
                    Fleet::class,
                    SpacecraftRump::class,
                    SpacecraftSystem::class
                )
            )
            ->setParameters([
                'location' => $location,
                'systemId' => SpacecraftSystemTypeEnum::SYSTEM_CLOAK->value
            ])
            ->getResult();
    }

    #[Override]
    public function getSuitableForShieldRegeneration(int $regenerationThreshold): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s ss
                WITH s.id = ss.spacecraft_id
                JOIN %s bp
                WITH s.plan_id = bp.id
                WHERE ss.system_type = :shieldType
                AND ss.mode < :modeOn
                AND s.schilde<s.max_schilde
                AND s.shield_regeneration_timer <= :regenerationThreshold
                AND (SELECT count(sc.id) FROM %s sc WHERE s.id = sc.spacecraft_id) >= bp.crew
                AND NOT EXISTS (SELECT a FROM %s a
                                WHERE a.location_id = s.location_id
                                AND a.anomaly_type_id = :anomalyType
                                AND a.remaining_ticks > 0)',
                Spacecraft::class,
                SpacecraftSystem::class,
                SpacecraftBuildplan::class,
                CrewAssignment::class,
                Anomaly::class
            )
        )->setParameters([
            'shieldType' => SpacecraftSystemTypeEnum::SYSTEM_SHIELDS->value,
            'modeOn' => SpacecraftSystemModeEnum::MODE_ON,
            'regenerationThreshold' => $regenerationThreshold,
            'anomalyType' => AnomalyTypeEnum::SUBSPACE_ELLIPSE
        ])->getResult();
    }

    #[Override]
    public function getPlayerSpacecraftsForTick(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s
                FROM %s s
                JOIN %s p
                WITH s.plan_id = p.id
                JOIN %s u
                WITH s.user_id = u.id
                WHERE s.user_id > :firstUserId
                AND (   ((SELECT count(sc.id)
                        FROM %s sc
                        WHERE sc.spacecraft_id = s.id) > 0)
                    OR
                        (s.state IN (:scrapping, :underConstruction))
                    OR
                        (p.crew = 0))
                AND (u.vac_active = :false OR u.vac_request_date > :vacationThreshold)',
                Spacecraft::class,
                SpacecraftBuildplan::class,
                User::class,
                CrewAssignment::class
            )
        )->setParameters([
            'underConstruction' => SpacecraftStateEnum::SHIP_STATE_UNDER_CONSTRUCTION,
            'scrapping' => SpacecraftStateEnum::SHIP_STATE_UNDER_SCRAPPING,
            'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS,
            'firstUserId' => UserEnum::USER_FIRST_ID,
            'false' => false
        ])->toIterable();
    }

    #[Override]
    public function getNpcSpacecraftsForTick(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.user_id BETWEEN 2 AND (:firstUserId - 1)',
                Spacecraft::class
            )
        )->setParameter('firstUserId', UserEnum::USER_FIRST_ID)->getResult();
    }

    #[Override]
    public function isCloakedSpacecraftAtLocation(
        SpacecraftInterface $spacecraft
    ): bool {

        $result = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(s.id) FROM %s s
                    WHERE s.location = :location
                    AND EXISTS (SELECT ss.id
                            FROM %s ss
                            WHERE s = ss.spacecraft
                            AND ss.system_type = %d
                            AND ss.mode > 1)
                    AND s.user != :ignoreUser',
                Spacecraft::class,
                SpacecraftSystem::class,
                SpacecraftSystemTypeEnum::SYSTEM_CLOAK->value
            )
        )->setParameters([
            'location' => $spacecraft->getLocation(),
            'ignoreUser' => $spacecraft->getUser()
        ])->getSingleScalarResult();

        return $result > 0;
    }

    #[Override]
    public function getRandomSpacecraftIdWithCrewByUser(int $userId): ?int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $result = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT s.id as id FROM stu_spacecraft s
                WHERE s.user_id = :userId
                AND EXISTS (SELECT sc.id
                            FROM stu_crew_assign sc
                            WHERE s.id = sc.spacecraft_id)
                ORDER BY RANDOM()
                LIMIT 1',
                $rsm
            )
            ->setParameters([
                'userId' => $userId
            ])
            ->getOneOrNullResult();

        return $result != null ? $result['id'] : null;
    }

    #[Override]
    public function getAllTractoringSpacecrafts(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.tractored_ship_id IS NOT NULL',
                Spacecraft::class
            )
        )->getResult();
    }

    #[Override]
    public function truncateAllSpacecrafts(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s s',
                Spacecraft::class
            )
        )->execute();
    }
}