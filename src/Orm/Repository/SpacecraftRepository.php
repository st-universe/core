<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Ship\Lib\TShipItem;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<Spacecraft>
 */
final class SpacecraftRepository extends EntityRepository implements SpacecraftRepositoryInterface
{
    #[\Override]
    public function findFresh(int $spacecraftId): ?Spacecraft
    {
        $spacecraftExists = (bool) $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT 1 FROM stu_spacecraft WHERE id = :spacecraftId',
            ['spacecraftId' => $spacecraftId]
        );

        if (!$spacecraftExists) {
            return null;
        }

        $spacecraft = $this->find($spacecraftId);
        if ($spacecraft === null) {
            return null;
        }

        $this->getEntityManager()->refresh($spacecraft);

        return $spacecraft;
    }

    #[\Override]
    public function save(Spacecraft $spacecraft): void
    {
        $em = $this->getEntityManager();

        $em->persist($spacecraft);
    }

    #[\Override]
    public function delete(Spacecraft $spacecraft): void
    {
        $em = $this->getEntityManager();

        $em->remove($spacecraft);
    }

    #[\Override]
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
                $specialAbilityId === SpacecraftRump::SPECIAL_ABILITY_COLONIZE ? 'AND bp.crew = 0' : ''
            )
        )->setParameters([
            'userId' => $userId,
            'specialAbilityId' => $specialAbilityId,
        ])->getSingleScalarResult();
    }

    #[\Override]
    public function getAmountByUserAndRump(int $userId, int $rumpId): int
    {
        return $this->count([
            'user_id' => $userId,
            'rump_id' => $rumpId,
        ]);
    }

    #[\Override]
    public function getByUser(User $user): array
    {
        return $this->findBy([
            'user_id' => $user,
        ]);
    }

    #[\Override]
    public function getSuitableForShieldRegeneration(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s sc
                WITH s = sc.spacecraft
                JOIN %s ss
                WITH s.id = ss.spacecraft_id
                JOIN %s bp
                WITH s.plan_id = bp.id
                WHERE ss.system_type = :shieldType
                AND ss.mode < :modeOn
                AND sc.shield < s.maxShield
                AND (SELECT count(ca.crew) FROM %s ca WHERE s = ca.spacecraft) >= bp.crew
                AND NOT EXISTS (SELECT a FROM %s a
                                WHERE a.location = s.location
                                AND a.anomaly_type_id in (:anomalyTypes)
                                AND a.remaining_ticks > 0)',
                Spacecraft::class,
                SpacecraftCondition::class,
                SpacecraftSystem::class,
                SpacecraftBuildplan::class,
                CrewAssignment::class,
                Anomaly::class
            )
        )->setParameters([
            'shieldType' => SpacecraftSystemTypeEnum::SHIELDS->value,
            'modeOn' => SpacecraftSystemModeEnum::MODE_ON->value,
            'anomalyTypes' => [AnomalyTypeEnum::SUBSPACE_ELLIPSE, AnomalyTypeEnum::ION_STORM]
        ])->getResult();
    }

    #[\Override]
    public function getPlayerSpacecraftsForTick(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s
                FROM %s s
                JOIN %s sc
                WITH s = sc.spacecraft
                JOIN %s p
                WITH s.plan_id = p.id
                JOIN %s u
                WITH s.user_id = u.id
                WHERE s.user_id > :firstUserId
                AND (   ((SELECT count(ca.crew)
                        FROM %s ca
                        WHERE ca.spacecraft = s) > 0)
                    OR
                        (sc.state IN (:scrapping, :underConstruction))
                    OR
                        (p.crew = 0))
                AND (u.vac_active = :false OR u.vac_request_date > :vacationThreshold)',
                Spacecraft::class,
                SpacecraftCondition::class,
                SpacecraftBuildplan::class,
                User::class,
                CrewAssignment::class
            )
        )->setParameters([
            'underConstruction' => SpacecraftStateEnum::UNDER_CONSTRUCTION,
            'scrapping' => SpacecraftStateEnum::UNDER_SCRAPPING,
            'vacationThreshold' => time() - UserConstants::VACATION_DELAY_IN_SECONDS,
            'firstUserId' => UserConstants::USER_FIRST_ID,
            'false' => false
        ])->toIterable();
    }

    #[\Override]
    public function getNpcSpacecraftsForTick(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.user_id BETWEEN 2 AND (:firstUserId - 1)',
                Spacecraft::class
            )
        )->setParameter('firstUserId', UserConstants::USER_FIRST_ID)->getResult();
    }

    #[\Override]
    public function isCloakedSpacecraftAtLocation(
        Spacecraft $spacecraft
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

    #[\Override]
    public function getSingleSpacecraftScannerResults(
        Spacecraft $spacecraft,
        bool $showCloaked = false,
        Map|StarSystemMap|null $field = null
    ): array {

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TShipItem::class, 's');
        TShipItem::addTSpacecraftItemFields($rsm);

        $location = $field ?? $spacecraft->getLocation();

        $query = $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT sp.id as shipid, s.fleet_id as fleetid, sp.rump_id as rumpid , ss.mode as warpstate,
                    twd.mode as tractorwarpstate, COALESCE(ss2.mode,0) as cloakstate, ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate,
                    COALESCE(ss5.mode,0) as rpgmodulestate,
                    sp.type as spacecrafttype, sp.name as shipname, sc.hull as hull, sp.max_hull as maxhull,
                    sc.shield as shield, sp.holding_web_id as webid, tw.finished_time as webfinishtime, u.id as userid, u.username,
                    r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_spacecraft_log sl WHERE sl.spacecraft_id = sp.id AND sl.is_private = :false AND sl.deleted IS NULL) as haslogbook,
                    (SELECT count(*) > 0 FROM stu_crew_assign ca WHERE ca.spacecraft_id = sp.id) as hascrew
                FROM stu_spacecraft sp
                JOIN stu_spacecraft_condition sc
                ON sp.id = sc.spacecraft_id
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
                LEFT JOIN stu_spacecraft_system ss5
                ON sp.id = ss5.spacecraft_id
                AND ss5.system_type = :rpgModuleType
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
            'rpgModuleType' => SpacecraftSystemTypeEnum::RPG_MODULE->value,
            'false' => false,
            'stationType' => SpacecraftTypeEnum::STATION->value
        ]);

        return $query->getResult();
    }

    #[\Override]
    public function getRandomSpacecraftWithCrewByUser(int $userId, array $excludedIds = []): ?Spacecraft
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $result = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT s.id as id FROM stu_spacecraft s
                WHERE s.user_id = :userId
                AND s.id NOT IN (:excludedIds)
                AND EXISTS (SELECT ca.crew_id
                            FROM stu_crew_assign ca
                            WHERE s.id = ca.spacecraft_id)
                ORDER BY RANDOM()
                LIMIT 1',
                $rsm
            )
            ->setParameters([
                'userId' => $userId,
                'excludedIds' => $excludedIds === [] ? [0] : $excludedIds
            ])
            ->getOneOrNullResult();

        return $result != null
            ? $this->findOneBy(['id' => $result['id']])
            : null;
    }

    #[\Override]
    public function getTractoringSpacecraft(Ship $tractoredShip): ?Spacecraft
    {
        return $this->findOneBy([
            'tractored_ship_id' => $tractoredShip->getId()
        ]);
    }

    #[\Override]
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

    #[\Override]
    public function truncateAllSpacecrafts(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s s',
                Spacecraft::class
            )
        )->execute();
    }

    #[\Override]
    public function getNearbySpacecraftsForWarpcoreTransfer(Spacecraft $spacecraft): Collection
    {
        $location = $spacecraft->getLocation();

        $spacecrafts = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.location = :location
                AND s.id != :spacecraftId',
                Spacecraft::class
            )
        )->setParameters([
            'location' => $location,
            'spacecraftId' => $spacecraft->getId()
        ])->getResult();

        $filteredSpacecrafts = array_filter($spacecrafts, function (Spacecraft $ship): bool {
            return !$ship->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE) &&
                !$ship->getSystemState(SpacecraftSystemTypeEnum::SHIELDS) && (
                    $ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::WARPCORE) || $ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SINGULARITY_REACTOR)
                );
        });

        return new ArrayCollection($filteredSpacecrafts);
    }

    #[\Override]
    public function getAdminLiveMapSpacecrafts(int $layerId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('name', 'name', 'string');
        $rsm->addScalarResult('type', 'type', 'string');
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('user_name', 'user_name', 'string');
        $rsm->addScalarResult('alliance_id', 'alliance_id', 'integer');
        $rsm->addScalarResult('alliance_name', 'alliance_name', 'string');
        $rsm->addScalarResult('rump_id', 'rump_id', 'integer');
        $rsm->addScalarResult('rump_name', 'rump_name', 'string');
        $rsm->addScalarResult('x', 'x', 'integer');
        $rsm->addScalarResult('y', 'y', 'integer');
        $rsm->addScalarResult('in_system', 'in_system', 'boolean');
        $rsm->addScalarResult('system_name', 'system_name', 'string');
        $rsm->addScalarResult('is_cloaked', 'is_cloaked', 'boolean');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT sp.id,
                    sp.name,
                    sp.type,
                    sp.user_id,
                    u.username as user_name,
                    al.id as alliance_id,
                    al.name as alliance_name,
                    sp.rump_id,
                    r.name as rump_name,
                    CASE WHEN map_field.id IS NOT NULL THEN location.cx ELSE parent_location.cx END as x,
                    CASE WHEN map_field.id IS NOT NULL THEN location.cy ELSE parent_location.cy END as y,
                    CASE WHEN system_field.id IS NULL THEN false ELSE true END as in_system,
                    systems.name as system_name,
                    CASE WHEN COALESCE(cloak_system.mode, 0) >= :cloakMode THEN true ELSE false END as is_cloaked
                FROM stu_spacecraft sp
                JOIN stu_location location
                ON sp.location_id = location.id
                LEFT JOIN stu_map map_field
                ON map_field.id = location.id
                LEFT JOIN stu_sys_map system_field
                ON system_field.id = location.id
                LEFT JOIN stu_map parent_map
                ON parent_map.systems_id = system_field.systems_id
                LEFT JOIN stu_location parent_location
                ON parent_location.id = parent_map.id
                LEFT JOIN stu_systems systems
                ON systems.id = system_field.systems_id
                JOIN stu_user u
                ON u.id = sp.user_id
                LEFT JOIN stu_alliances al
                ON al.id = u.allys_id
                JOIN stu_rump r
                ON r.id = sp.rump_id
                LEFT JOIN stu_spacecraft_system cloak_system
                ON cloak_system.spacecraft_id = sp.id
                AND cloak_system.system_type = :cloakType
                WHERE COALESCE(location.layer_id, parent_location.layer_id) = :layerId
                AND (map_field.id IS NOT NULL OR parent_map.id IS NOT NULL)
                ORDER BY sp.id ASC',
                $rsm
            )
            ->setParameters([
                'layerId' => $layerId,
                'cloakType' => SpacecraftSystemTypeEnum::CLOAK->value,
                'cloakMode' => SpacecraftSystemModeEnum::MODE_ON->value
            ])
            ->getResult();
    }

    #[\Override]
    public function getUserStarmapSpacecrafts(
        int $userId,
        int $layerId,
        ?int $allianceId,
        bool $includeAlliance,
        bool $includeFullLayer
    ): array {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('name', 'name', 'string');
        $rsm->addScalarResult('type', 'type', 'string');
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('user_name', 'user_name', 'string');
        $rsm->addScalarResult('alliance_id', 'alliance_id', 'integer');
        $rsm->addScalarResult('alliance_name', 'alliance_name', 'string');
        $rsm->addScalarResult('rump_id', 'rump_id', 'integer');
        $rsm->addScalarResult('rump_name', 'rump_name', 'string');
        $rsm->addScalarResult('x', 'x', 'integer');
        $rsm->addScalarResult('y', 'y', 'integer');
        $rsm->addScalarResult('in_system', 'in_system', 'boolean');
        $rsm->addScalarResult('system_name', 'system_name', 'string');
        $rsm->addScalarResult('is_cloaked', 'is_cloaked', 'boolean');
        $rsm->addScalarResult('hull', 'hull', 'integer');
        $rsm->addScalarResult('max_hull', 'max_hull', 'integer');
        $rsm->addScalarResult('shield', 'shield', 'integer');
        $rsm->addScalarResult('max_shield', 'max_shield', 'integer');
        $rsm->addScalarResult('eps', 'eps', 'integer');
        $rsm->addScalarResult('max_eps', 'max_eps', 'integer');
        $rsm->addScalarResult('warpdrive', 'warpdrive', 'integer');
        $rsm->addScalarResult('max_warpdrive', 'max_warpdrive', 'integer');
        $rsm->addScalarResult('alert_state', 'alert_state', 'integer');

        $visibilityCondition = $includeAlliance && $allianceId !== null
            ? '((sp.user_id = :userId AND sp.type IN (:ownShipType, :ownStationType))
                OR (u.allys_id = :allianceId AND sp.type IN (:allianceShipType, :allianceStationType)))'
            : '(sp.user_id = :userId AND sp.type IN (:ownShipType, :ownStationType))';
        $visibleJoin = $includeFullLayer
            ? ''
            : 'JOIN stu_user_map visible_map
                ON visible_map.user_id = :userId
                AND visible_map.layer_id = :layerId
                AND visible_map.cx = ship_map.x
                AND visible_map.cy = ship_map.y';
        $parameters = [
            'userId' => $userId,
            'layerId' => $layerId,
            'ownShipType' => SpacecraftTypeEnum::SHIP->value,
            'ownStationType' => SpacecraftTypeEnum::STATION->value,
            'cloakType' => SpacecraftSystemTypeEnum::CLOAK->value,
            'cloakMode' => SpacecraftSystemModeEnum::MODE_ON->value,
            'epsType' => SpacecraftSystemTypeEnum::EPS->value,
            'warpdriveType' => SpacecraftSystemTypeEnum::WARPDRIVE->value,
            'computerType' => SpacecraftSystemTypeEnum::COMPUTER->value
        ];
        if ($includeAlliance && $allianceId !== null) {
            $parameters['allianceId'] = $allianceId;
            $parameters['allianceShipType'] = SpacecraftTypeEnum::SHIP->value;
            $parameters['allianceStationType'] = SpacecraftTypeEnum::STATION->value;
        }

        return $this->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'SELECT ship_map.*
                    FROM (
                        SELECT sp.id,
                            sp.name,
                            sp.type,
                            sp.user_id,
                            u.username as user_name,
                            al.id as alliance_id,
                            al.name as alliance_name,
                            sp.rump_id,
                            r.name as rump_name,
                            CASE WHEN map_field.id IS NOT NULL THEN location.cx ELSE parent_location.cx END as x,
                            CASE WHEN map_field.id IS NOT NULL THEN location.cy ELSE parent_location.cy END as y,
                            CASE WHEN system_field.id IS NULL THEN false ELSE true END as in_system,
                            systems.name as system_name,
                            CASE WHEN COALESCE(cloak_system.mode, 0) >= :cloakMode THEN true ELSE false END as is_cloaked,
                            sc.hull,
                            sp.max_hull,
                            sc.shield,
                            sp.max_shield,
                            COALESCE((NULLIF(eps_system.data, \'\')::json->>\'eps\')::int, 0) as eps,
                            COALESCE(CEIL((NULLIF(eps_system.data, \'\')::json->>\'maxEps\')::numeric * eps_system.status / 100), 0)::int as max_eps,
                            COALESCE((NULLIF(warp_system.data, \'\')::json->>\'wd\')::int, 0) as warpdrive,
                            COALESCE(CEIL((NULLIF(warp_system.data, \'\')::json->>\'maxwd\')::numeric * warp_system.status / 100), 0)::int as max_warpdrive,
                            COALESCE((NULLIF(computer_system.data, \'\')::json->>\'alertState\')::int, 1) as alert_state
                        FROM stu_spacecraft sp
                        JOIN stu_spacecraft_condition sc
                        ON sc.spacecraft_id = sp.id
                        JOIN stu_location location
                        ON sp.location_id = location.id
                        LEFT JOIN stu_map map_field
                        ON map_field.id = location.id
                        LEFT JOIN stu_sys_map system_field
                        ON system_field.id = location.id
                        LEFT JOIN stu_map parent_map
                        ON parent_map.systems_id = system_field.systems_id
                        LEFT JOIN stu_location parent_location
                        ON parent_location.id = parent_map.id
                        LEFT JOIN stu_systems systems
                        ON systems.id = system_field.systems_id
                        JOIN stu_user u
                        ON u.id = sp.user_id
                        LEFT JOIN stu_alliances al
                        ON al.id = u.allys_id
                        JOIN stu_rump r
                        ON r.id = sp.rump_id
                        LEFT JOIN stu_spacecraft_system cloak_system
                        ON cloak_system.spacecraft_id = sp.id
                        AND cloak_system.system_type = :cloakType
                        LEFT JOIN stu_spacecraft_system eps_system
                        ON eps_system.spacecraft_id = sp.id
                        AND eps_system.system_type = :epsType
                        LEFT JOIN stu_spacecraft_system warp_system
                        ON warp_system.spacecraft_id = sp.id
                        AND warp_system.system_type = :warpdriveType
                        LEFT JOIN stu_spacecraft_system computer_system
                        ON computer_system.spacecraft_id = sp.id
                        AND computer_system.system_type = :computerType
                        WHERE COALESCE(location.layer_id, parent_location.layer_id) = :layerId
                        AND (map_field.id IS NOT NULL OR parent_map.id IS NOT NULL)
                        AND %s
                    ) ship_map
                    %s
                    ORDER BY ship_map.y ASC, ship_map.x ASC, ship_map.id ASC',
                    $visibilityCondition,
                    $visibleJoin
                ),
                $rsm
            )
            ->setParameters($parameters)
            ->getResult();
    }

    #[\Override]
    public function getUserStarmapRealtimeSensorRanges(int $userId, int $layerId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('source_id', 'source_id', 'integer');
        $rsm->addScalarResult('x', 'x', 'integer');
        $rsm->addScalarResult('y', 'y', 'integer');
        $rsm->addScalarResult('sensor_range', 'sensor_range', 'integer');
        $rsm->addScalarResult('tachyon_range', 'tachyon_range', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'SELECT source_id, x, y, sensor_range, tachyon_range
                    FROM (%s) sensor_sources
                    WHERE sensor_range > 0 OR tachyon_range > 0
                    ORDER BY y ASC, x ASC, source_id ASC',
                    $this->getRealtimeSensorSourceSql()
                ),
                $rsm
            )
            ->setParameters($this->getRealtimeSensorParameters($userId, $layerId))
            ->getResult();
    }

    #[\Override]
    public function getUserStarmapRealtimeSpacecrafts(int $userId, int $layerId): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('name', 'name', 'string');
        $rsm->addScalarResult('type', 'type', 'string');
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('user_name', 'user_name', 'string');
        $rsm->addScalarResult('alliance_id', 'alliance_id', 'integer');
        $rsm->addScalarResult('alliance_name', 'alliance_name', 'string');
        $rsm->addScalarResult('rump_id', 'rump_id', 'integer');
        $rsm->addScalarResult('rump_name', 'rump_name', 'string');
        $rsm->addScalarResult('x', 'x', 'integer');
        $rsm->addScalarResult('y', 'y', 'integer');
        $rsm->addScalarResult('in_system', 'in_system', 'boolean');
        $rsm->addScalarResult('system_name', 'system_name', 'string');
        $rsm->addScalarResult('is_cloaked', 'is_cloaked', 'boolean');
        $rsm->addScalarResult('hull', 'hull', 'integer');
        $rsm->addScalarResult('max_hull', 'max_hull', 'integer');
        $rsm->addScalarResult('shield', 'shield', 'integer');
        $rsm->addScalarResult('max_shield', 'max_shield', 'integer');
        $rsm->addScalarResult('eps', 'eps', 'integer');
        $rsm->addScalarResult('max_eps', 'max_eps', 'integer');
        $rsm->addScalarResult('warpdrive', 'warpdrive', 'integer');
        $rsm->addScalarResult('max_warpdrive', 'max_warpdrive', 'integer');
        $rsm->addScalarResult('alert_state', 'alert_state', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'WITH sensor_ranges AS (
                        SELECT source_id, x, y, GREATEST(sensor_range, tachyon_range) as visibility_range
                        FROM (%s) sensor_sources
                        WHERE sensor_range > 0 OR tachyon_range > 0
                    ),
                    contact_positions AS (
                        %s
                    )
                    SELECT DISTINCT ON (contact_positions.id) contact_positions.*
                    FROM contact_positions
                    JOIN sensor_ranges
                    ON contact_positions.x BETWEEN sensor_ranges.x - sensor_ranges.visibility_range AND sensor_ranges.x + sensor_ranges.visibility_range
                    AND contact_positions.y BETWEEN sensor_ranges.y - sensor_ranges.visibility_range AND sensor_ranges.y + sensor_ranges.visibility_range
                    ORDER BY contact_positions.id ASC',
                    $this->getRealtimeSensorSourceSql(),
                    $this->getRealtimeContactPositionSql()
                ),
                $rsm
            )
            ->setParameters($this->getRealtimeSensorParameters($userId, $layerId) + [
                'shipType' => SpacecraftTypeEnum::SHIP->value,
                'contactStationType' => SpacecraftTypeEnum::STATION->value,
                'cloakType' => SpacecraftSystemTypeEnum::CLOAK->value,
                'cloakMode' => SpacecraftSystemModeEnum::MODE_ON->value,
                'epsType' => SpacecraftSystemTypeEnum::EPS->value,
                'warpdriveType' => SpacecraftSystemTypeEnum::WARPDRIVE->value,
                'computerType' => SpacecraftSystemTypeEnum::COMPUTER->value,
                'nooneUserId' => UserConstants::USER_NOONE
            ])
            ->getResult();
    }

    private function getRealtimeSensorSourceSql(): string
    {
        return 'SELECT src.id as source_id,
                CASE WHEN map_field.id IS NOT NULL THEN source_location.cx ELSE parent_location.cx END as x,
                CASE WHEN map_field.id IS NOT NULL THEN source_location.cy ELSE parent_location.cy END as y,
                COALESCE(CEIL((NULLIF(lss_system.data, \'\')::json->>\'sensorRange\')::numeric * lss_system.status / 100), 0)::int as sensor_range,
                CASE WHEN COALESCE(tachyon_system.mode, 0) >= :tachyonMode THEN 7 ELSE 0 END as tachyon_range
            FROM stu_spacecraft src
            JOIN stu_location source_location
            ON source_location.id = src.location_id
            LEFT JOIN stu_map map_field
            ON map_field.id = source_location.id
            LEFT JOIN stu_sys_map system_field
            ON system_field.id = source_location.id
            LEFT JOIN stu_map parent_map
            ON parent_map.systems_id = system_field.systems_id
            LEFT JOIN stu_location parent_location
            ON parent_location.id = parent_map.id
            JOIN stu_rump source_rump
            ON source_rump.id = src.rump_id
            JOIN stu_user source_owner
            ON source_owner.id = src.user_id
            JOIN stu_spacecraft_system lss_system
            ON lss_system.spacecraft_id = src.id
            AND lss_system.system_type = :lssType
            LEFT JOIN stu_spacecraft_system tachyon_system
            ON tachyon_system.spacecraft_id = src.id
            AND tachyon_system.system_type = :tachyonType
            LEFT JOIN stu_spacecraft_system uplink_system
            ON uplink_system.spacecraft_id = src.id
            AND uplink_system.system_type = :uplinkType
            WHERE src.type = :sourceStationType
            AND COALESCE(source_location.layer_id, parent_location.layer_id) = :layerId
            AND (map_field.id IS NOT NULL OR parent_map.id IS NOT NULL)
            AND lss_system.status > 0
            AND (source_owner.vac_active = :false OR source_owner.vac_request_date > :vacationThreshold)
            AND (
                (src.user_id = :userId AND source_rump.role_id IN (:baseRole, :outpostRole))
                OR (
                    source_rump.role_id = :sensorRole
                    AND COALESCE(uplink_system.mode, 0) >= :uplinkMode
                    AND (
                        src.user_id = :userId
                        OR EXISTS (
                            SELECT 1
                            FROM stu_crew_assign uplink_crew
                            WHERE uplink_crew.spacecraft_id = src.id
                            AND uplink_crew.user_id = :userId
                        )
                    )
                )
            )';
    }

    private function getRealtimeContactPositionSql(): string
    {
        return 'SELECT sp.id,
                sp.name,
                sp.type,
                sp.user_id,
                u.username as user_name,
                al.id as alliance_id,
                al.name as alliance_name,
                sp.rump_id,
                r.name as rump_name,
                CASE WHEN map_field.id IS NOT NULL THEN location.cx ELSE parent_location.cx END as x,
                CASE WHEN map_field.id IS NOT NULL THEN location.cy ELSE parent_location.cy END as y,
                CASE WHEN system_field.id IS NULL THEN false ELSE true END as in_system,
                systems.name as system_name,
                CASE WHEN COALESCE(cloak_system.mode, 0) >= :cloakMode THEN true ELSE false END as is_cloaked,
                sc.hull,
                sp.max_hull,
                sc.shield,
                sp.max_shield,
                COALESCE((NULLIF(eps_system.data, \'\')::json->>\'eps\')::int, 0) as eps,
                COALESCE(CEIL((NULLIF(eps_system.data, \'\')::json->>\'maxEps\')::numeric * eps_system.status / 100), 0)::int as max_eps,
                COALESCE((NULLIF(warp_system.data, \'\')::json->>\'wd\')::int, 0) as warpdrive,
                COALESCE(CEIL((NULLIF(warp_system.data, \'\')::json->>\'maxwd\')::numeric * warp_system.status / 100), 0)::int as max_warpdrive,
                COALESCE((NULLIF(computer_system.data, \'\')::json->>\'alertState\')::int, 1) as alert_state
            FROM stu_spacecraft sp
            JOIN stu_spacecraft_condition sc
            ON sc.spacecraft_id = sp.id
            JOIN stu_location location
            ON sp.location_id = location.id
            LEFT JOIN stu_map map_field
            ON map_field.id = location.id
            LEFT JOIN stu_sys_map system_field
            ON system_field.id = location.id
            LEFT JOIN stu_map parent_map
            ON parent_map.systems_id = system_field.systems_id
            LEFT JOIN stu_location parent_location
            ON parent_location.id = parent_map.id
            LEFT JOIN stu_systems systems
            ON systems.id = system_field.systems_id
            JOIN stu_user u
            ON u.id = sp.user_id
            LEFT JOIN stu_alliances al
            ON al.id = u.allys_id
            JOIN stu_rump r
            ON r.id = sp.rump_id
            LEFT JOIN stu_spacecraft_system cloak_system
            ON cloak_system.spacecraft_id = sp.id
            AND cloak_system.system_type = :cloakType
            LEFT JOIN stu_spacecraft_system eps_system
            ON eps_system.spacecraft_id = sp.id
            AND eps_system.system_type = :epsType
            LEFT JOIN stu_spacecraft_system warp_system
            ON warp_system.spacecraft_id = sp.id
            AND warp_system.system_type = :warpdriveType
            LEFT JOIN stu_spacecraft_system computer_system
            ON computer_system.spacecraft_id = sp.id
            AND computer_system.system_type = :computerType
            WHERE COALESCE(location.layer_id, parent_location.layer_id) = :layerId
            AND (map_field.id IS NOT NULL OR parent_map.id IS NOT NULL)
            AND sp.type IN (:shipType, :contactStationType)
            AND sp.user_id != :nooneUserId';
    }

    /**
     * @return array<string, mixed>
     */
    private function getRealtimeSensorParameters(int $userId, int $layerId): array
    {
        return [
            'userId' => $userId,
            'layerId' => $layerId,
            'sourceStationType' => SpacecraftTypeEnum::STATION->value,
            'baseRole' => SpacecraftRumpRoleEnum::BASE->value,
            'outpostRole' => SpacecraftRumpRoleEnum::OUTPOST->value,
            'sensorRole' => SpacecraftRumpRoleEnum::SENSOR->value,
            'lssType' => SpacecraftSystemTypeEnum::LSS->value,
            'tachyonType' => SpacecraftSystemTypeEnum::TACHYON_SCANNER->value,
            'tachyonMode' => SpacecraftSystemModeEnum::MODE_ON->value,
            'uplinkType' => SpacecraftSystemTypeEnum::UPLINK->value,
            'uplinkMode' => SpacecraftSystemModeEnum::MODE_ON->value,
            'vacationThreshold' => time() - UserConstants::VACATION_DELAY_IN_SECONDS,
            'false' => false
        ];
    }
}
