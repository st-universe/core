<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\TFleetShipItem;
use Stu\Module\Ship\Lib\TShipItem;
use Stu\Module\Spacecraft\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMapInterface;
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
            'shieldType' => SpacecraftSystemTypeEnum::SHIELDS->value,
            'modeOn' => SpacecraftSystemModeEnum::MODE_ON->value,
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
                SpacecraftSystemTypeEnum::CLOAK->value
            )
        )->setParameters([
            'location' => $spacecraft->getLocation(),
            'ignoreUser' => $spacecraft->getUser()
        ])->getSingleScalarResult();

        return $result > 0;
    }

    #[Override]
    public function getSingleSpacecraftScannerResults(
        SpacecraftInterface $spacecraft,
        bool $showCloaked = false,
        MapInterface|StarSystemMapInterface|null $field = null
    ): array {

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TShipItem::class, 's');
        TShipItem::addTSpacecraftItemFields($rsm);

        $location = $field ?? $spacecraft->getLocation();

        $query = $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT sp.id as shipid, s.fleet_id as fleetid, sp.rump_id as rumpid , ss.mode as warpstate,
                    twd.mode as tractorwarpstate, COALESCE(ss2.mode,0) as cloakstate, ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate,
                    sp.type as spacecrafttype, sp.name as shipname, sp.huelle as hull, sp.max_huelle as maxhull,
                    sp.schilde as shield, sp.holding_web_id as webid, tw.finished_time as webfinishtime, u.id as userid, u.username,
                    r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_ship_log sl WHERE sl.spacecraft_id = sp.id AND sl.is_private = :false) as haslogbook,
                    (SELECT count(*) > 0 FROM stu_crew_assign ca WHERE ca.spacecraft_id = sp.id) as hascrew
                FROM stu_spacecraft sp
                LEFT JOIN stu_ship s
                ON s.id = sp.id
                LEFT JOIN stu_spacecraft_system ss
                ON sp.id = ss.spacecraft_id
                AND ss.system_type = :warpdriveType
                LEFT JOIN stu_spacecraft tractor
                ON tractor.tractored_ship_id = s.id
                LEFT JOIN stu_spacecraft_system twd
                ON tractor.id = twd.spacecraft_id
                AND twd.system_type = :warpdriveType
                LEFT JOIN stu_spacecraft_system ss2
                ON sp.id = ss2.spacecraft_id
                AND ss2.system_type = :cloakType
                LEFT JOIN stu_spacecraft_system ss3
                ON sp.id = ss3.spacecraft_id
                AND ss3.system_type = :shieldType
                LEFT JOIN stu_spacecraft_system ss4
                ON sp.id = ss4.spacecraft_id
                AND ss4.system_type = :uplinkType
                JOIN stu_rump r
                ON sp.rump_id = r.id
                LEFT OUTER JOIN stu_tholian_web tw
                ON sp.holding_web_id = tw.id
                JOIN stu_user u
                ON sp.user_id = u.id
                WHERE sp.location_id = :locationId
                AND sp.id != :ignoreId
                AND s.fleet_id IS NULL
                AND sp.type != :stationType
                %s
                ORDER BY r.category_id ASC, r.role_id ASC, r.id ASC, sp.name ASC',
                $showCloaked ? '' : sprintf(' AND (sp.user_id = %d OR COALESCE(ss2.mode,0) < %d) ', $spacecraft->getUser()->getId(), SpacecraftSystemModeEnum::MODE_ON->value)
            ),
            $rsm
        )->setParameters([
            'locationId' => $location->getId(),
            'ignoreId' => $spacecraft->getId(),
            'cloakType' => SpacecraftSystemTypeEnum::CLOAK->value,
            'warpdriveType' => SpacecraftSystemTypeEnum::WARPDRIVE->value,
            'shieldType' => SpacecraftSystemTypeEnum::SHIELDS->value,
            'uplinkType' => SpacecraftSystemTypeEnum::UPLINK->value,
            'false' => false,
            'stationType' => SpacecraftTypeEnum::STATION->value
        ]);

        return $query->getResult();
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
