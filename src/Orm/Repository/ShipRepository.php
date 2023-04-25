<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\ShipSystem;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<Ship>
 */
final class ShipRepository extends EntityRepository implements ShipRepositoryInterface
{
    public function prototype(): ShipInterface
    {
        return new Ship();
    }

    public function save(ShipInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(ShipInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    public function getAmountByUserAndSpecialAbility(
        int $userId,
        int $specialAbilityId
    ): int {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(s)
                FROM %s s
                JOIN %s bp
                WITH s.plans_id = bp.id
                WHERE s.user_id = :userId AND s.rumps_id IN (
                    SELECT rs.rumps_id FROM %s rs WHERE rs.special = :specialAbilityId
                )
                %s',
                Ship::class,
                ShipBuildplan::class,
                ShipRumpSpecial::class,
                $specialAbilityId === ShipRumpSpecialAbilityEnum::COLONIZE ? 'AND bp.crew = 0' : ''
            )
        )->setParameters([
            'userId' => $userId,
            'specialAbilityId' => $specialAbilityId,
        ])->getSingleScalarResult();
    }

    public function getAmountByUserAndRump(int $userId, int $shipRumpId): int
    {
        return $this->count([
            'user_id' => $userId,
            'rumps_id' => $shipRumpId,
        ]);
    }

    public function getByUser(UserInterface $user): iterable
    {
        return $this->findBy([
            'user_id' => $user,
        ]);
    }

    public function getByUserAndRump(int $userId, int $rumpId): array
    {
        return $this->findBy([
            'user_id' => $userId,
            'rumps_id' => $rumpId
        ], [
            'map_id' => 'asc',
            'starsystem_map_id' => 'asc',
            'fleets_id' => 'asc',
            'is_fleet_leader' => 'desc'
        ]);
    }

    public function getPossibleFleetMembers(ShipInterface $fleetLeader): iterable
    {
        $isSystem = $fleetLeader->getSystem() !== null;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.%s = :mapId
                AND s.fleets_id IS NULL
                AND s.user_id = :userId
                AND s.type = :type
                ORDER BY s.rumps_id ASC, s.name ASC',
                Ship::class,
                $isSystem ? 'starsystem_map_id' : 'map_id'
            )
        )->setParameters([
            'userId' => $fleetLeader->getUser()->getId(),
            'type' => SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP,
            'mapId' => $isSystem ? $fleetLeader->getStarsystemMap()->getId() : $fleetLeader->getMap()->getId()
        ])->getResult();
    }

    public function getByLocationAndUser(?StarSystemMapInterface $starSystemMap, ?MapInterface $map, UserInterface $user): array
    {
        return $this->findBy([
            'user_id' => $user->getId(),
            'starsystem_map_id' => $starSystemMap !== null ? $starSystemMap->getId() : null,
            'map_id' => $map !== null ? $map->getId() : null
        ], [
            'fleets_id' => 'desc',
            'is_fleet_leader' => 'desc',
            'id' => 'desc'
        ]);
    }

    public function getByLocation(
        ?StarSystemMapInterface $starSystemMap,
        ?MapInterface $map
    ): array {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s FROM %s s
                    LEFT JOIN %s f
                    WITH s.fleets_id = f.id
                    JOIN %s r
                    WITH s.rumps_id = r.id
                    WHERE s.%s = :mapId
                    AND NOT EXISTS (SELECT ss.id
                                        FROM %s ss
                                        WHERE s.id = ss.ship_id
                                        AND ss.system_type = :systemId
                                        AND ss.mode > 1)
                    ORDER BY s.is_destroyed ASC, f.sort DESC, f.id DESC, s.is_fleet_leader DESC,
                    r.category_id ASC, r.role_id ASC, r.id ASC, s.name ASC',
                    Ship::class,
                    Fleet::class,
                    ShipRump::class,
                    $starSystemMap === null ? 'map_id' : 'starsystem_map_id',
                    ShipSystem::class
                )
            )
            ->setParameters([
                'mapId' => $starSystemMap === null ? $map->getId() : $starSystemMap->getId(),
                'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
            ])
            ->getResult();
    }

    public function getForeignStationsInBroadcastRange(ShipInterface $ship): array
    {
        $systemMap = $ship->getStarsystemMap();
        $map = $ship->getMap();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s FROM %s s
                     LEFT JOIN %s m
                     WITH s.map_id = m.id
                     LEFT JOIN %s sm
                     WITH s.starsystem_map_id = sm.id
                     WHERE s.user_id NOT IN (:ignoreIds)
                     AND s.type = :spacecraftType
                     AND (:cx = 0 OR (m.cx BETWEEN (:cx - 1) AND (:cx + 1)
                        AND m.cy BETWEEN (:cy - 1) AND (:cy + 1)))
                     AND (:systemId = 0 OR (sm.systems_id = :systemId
                        AND sm.sx BETWEEN (:sx - 1) AND (:sx + 1)
                        AND sm.sy BETWEEN (:sy - 1) AND (:sy + 1)))',
                    Ship::class,
                    Map::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'ignoreIds' => [$ship->getUser()->getId(), GameEnum::USER_NOONE],
                'spacecraftType' => SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION,
                'systemId' => $systemMap === null ? 0 : $systemMap->getSystem()->getId(),
                'sx' => $systemMap === null ? 0 : $systemMap->getSx(),
                'sy' => $systemMap === null ? 0 : $systemMap->getSy(),
                'cx' => $map === null ? 0 : $map->getCx(),
                'cy' => $map === null ? 0 : $map->getCy()
            ])
            ->getResult();
    }

    public function getShipsForAlertRed(
        ShipInterface $ship
    ): iterable {
        $isSystem = $ship->getSystem() !== null;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s u
                WITH s.user_id = u.id
                WHERE s.alvl = :alertRed
                AND s.user_id != :ignoreId
                AND s.%s = :mapId
                AND NOT EXISTS (SELECT ss.id
                                FROM %s ss
                                WHERE s.id = ss.ship_id
                                AND ss.system_type = :cloakSystemId
                                AND ss.mode > 1)
                AND NOT EXISTS (SELECT ss2.id
                                FROM %s ss2
                                WHERE s.id = ss2.ship_id
                                AND ss2.system_type = :warpSystemId
                                AND ss2.mode > 1)
                AND (u.vac_active = false OR u.vac_request_date > :vacationThreshold)',
                Ship::class,
                User::class,
                $isSystem ? 'starsystem_map_id' : 'map_id',
                ShipSystem::class,
                ShipSystem::class
            )
        )->setParameters([
            'alertRed' => ShipAlertStateEnum::ALERT_RED,
            'mapId' => $isSystem ? $ship->getStarsystemMap()->getId() : $ship->getMap()->getId(),
            'ignoreId' => $ship->getUser()->getId(),
            'cloakSystemId' => ShipSystemTypeEnum::SYSTEM_CLOAK,
            'warpSystemId' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS
        ])->getResult();
    }

    public function getTradePostsWithoutDatabaseEntry(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.database_id is null AND s.trade_post_id > 0',
                Ship::class
            )
        )->getResult();
    }

    public function getByUserAndFleetAndType(int $userId, ?int $fleetId, int $type): array
    {
        return $this->findBy(
            [
                'user_id' => $userId,
                'fleets_id' => $fleetId,
                'type' => $type,
            ],
            $type === SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION ? ['max_huelle' => 'desc', 'id' => 'asc'] : ['id' => 'asc']
        );
    }

    public function getByUplink(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s sc
                WITH s.id = sc.ship_id
                JOIN %s c
                WITH sc.crew_id = c.id
                JOIN %s ss
                WITH ss.ship_id = s.id
                JOIN %s u
                WITH s.user_id = u.id
                WHERE s.user_id != :userId
                AND c.user_id = :userId
                AND ss.system_type = :systemType
                AND ss.mode >= :mode
                AND (u.vac_active = false OR u.vac_request_date > :vacationThreshold)',
                Ship::class,
                ShipCrew::class,
                Crew::class,
                ShipSystem::class,
                User::class
            )
        )->setParameters([
            'userId' => $userId,
            'systemType' => ShipSystemTypeEnum::SYSTEM_UPLINK,
            'mode' => ShipSystemModeEnum::MODE_ON,
            'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS
        ])
            ->getResult();
    }

    public function getWithTradeLicensePayment(
        int $userId,
        int $tradePostShipId,
        int $commodityId,
        int $amount
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.user_id = :userId AND s.dock = :tradePostShipId AND s.id IN (
                    SELECT st.ship_id FROM %s st WHERE st.commodity_id = :commodityId AND st.count >= :amount
                )',
                Ship::class,
                Storage::class
            )
        )->setParameters([
            'userId' => $userId,
            'tradePostShipId' => $tradePostShipId,
            'commodityId' => $commodityId,
            'amount' => $amount,
        ])->getResult();
    }

    public function getSuitableForShildRegeneration(int $regenerationThreshold): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s ss
                WITH s.id = ss.ship_id
                JOIN %s bp
                WITH s.plans_id = bp.id
                WHERE ss.system_type = :shieldType
                AND ss.mode < :modeOn
                AND s.is_destroyed = :destroyedState
                AND s.schilde<s.max_schilde
                AND s.shield_regeneration_timer <= :regenerationThreshold
                AND (SELECT count(sc.id) FROM %s sc WHERE s.id = sc.ship_id) >= bp.crew',
                Ship::class,
                ShipSystem::class,
                ShipBuildplan::class,
                ShipCrew::class
            )
        )->setParameters([
            'shieldType' => ShipSystemTypeEnum::SYSTEM_SHIELDS,
            'modeOn' => ShipSystemModeEnum::MODE_ON,
            'regenerationThreshold' => $regenerationThreshold,
            'destroyedState' => 0,
        ])->getResult();
    }

    public function getEscapePods(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                LEFT JOIN %s sr
                WITH s.rumps_id = sr.id
                WHERE sr.category_id = :categoryId',
                Ship::class,
                ShipRump::class
            )
        )->setParameters([
            'categoryId' => ShipRumpEnum::SHIP_CATEGORY_ESCAPE_PODS
        ])->getResult();
    }

    public function getEscapePodsByCrewOwner(int $userId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                LEFT JOIN %s sr
                WITH s.rumps_id = sr.id
                LEFT JOIN %s sc
                WITH sc.ship_id = s.id
                WHERE sr.category_id = :categoryId
                AND sc.user_id = :userId',
                Ship::class,
                ShipRump::class,
                ShipCrew::class
            )
        )->setParameters([
            'categoryId' => ShipRumpEnum::SHIP_CATEGORY_ESCAPE_PODS,
            'userId' => $userId
        ])->getResult();
    }

    public function getDebrisFields(): iterable
    {
        return $this->findBy([
            'is_destroyed' => true,
        ]);
    }

    public function getStationConstructions(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s r
                WITH s.rumps_id = r.id
                WHERE s.user_id > :firstUserId
                AND r.category_id = :catId',
                Ship::class,
                ShipRump::class
            )
        )->setParameters([
            'catId' => ShipRumpEnum::SHIP_CATEGORY_CONSTRUCTION,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])
            ->getResult();
    }

    public function getPlayerShipsForTick(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s
                FROM %s s
                JOIN %s p
                WITH s.plans_id = p.id
                JOIN %s u
                WITH s.user_id = u.id
                WHERE s.user_id > :firstUserId
                AND (   ((SELECT count(sc.id)
                        FROM %s sc
                        WHERE sc.ship_id = s.id) > 0)
                    OR
                        (s.state IN (:scrapping, :underConstruction))
                    OR
                        (p.crew = 0))
                AND (u.vac_active = false OR u.vac_request_date > :vacationThreshold)',
                Ship::class,
                ShipBuildplan::class,
                User::class,
                ShipCrew::class
            )
        )->setParameters([
            'underConstruction' => ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION,
            'scrapping' => ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING,
            'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])->toIterable();
    }

    public function getNpcShipsForTick(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.user_id BETWEEN 2 AND (:firstUserId - 1)',
                Ship::class
            )
        )->setParameter('firstUserId', UserEnum::USER_FIRST_ID)->getResult();
    }

    private const FLIGHT_SIGNATURE_STAR_COUNT =
    ',(select count(distinct fs1.ship_id) from stu_flight_sig fs1
    where fs1.starsystem_map_id = a.id
    AND fs1.user_id != %1$d
    AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
    AND fs1.time > %2$d) as d1c,
    (select count(distinct fs2.ship_id) from stu_flight_sig fs2
    where fs2.starsystem_map_id = a.id
    AND fs2.user_id != %1$d
    AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
    AND fs2.time > %2$d) as d2c,
    (select count(distinct fs3.ship_id) from stu_flight_sig fs3
    where fs3.starsystem_map_id = a.id
    AND fs3.user_id != %1$d
    AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
    AND fs3.time > %2$d) as d3c,
    (select count(distinct fs4.ship_id) from stu_flight_sig fs4
    where fs4.starsystem_map_id = a.id
    AND fs4.user_id != %1$d
    AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
    AND fs4.time > %2$d) as d4c ';

    public function getSensorResultInnerSystem(
        ShipInterface $ship,
        int $ignoreId,
        StarSystemInterface $system = null
    ): iterable {
        $doSubspace = $ship->getSubspaceState();
        $map = $ship->getStarsystemMap();
        $sensorRange = $ship->getSensorRange();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('sysid', 'sysid', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('cloakcount', 'cloakcount', 'integer');
        $rsm->addScalarResult('shieldstate', 'shieldstate', 'boolean');
        $rsm->addScalarResult('type', 'type', 'integer');

        if ($doSubspace) {
            $rsm->addScalarResult('d1c', 'd1c', 'integer');
            $rsm->addScalarResult('d2c', 'd2c', 'integer');
            $rsm->addScalarResult('d3c', 'd3c', 'integer');
            $rsm->addScalarResult('d4c', 'd4c', 'integer');

            $maxAge = time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED;
        }

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT a.id, a.sx as posx, a.sy AS posy, a.systems_id AS sysid, d.type,
                (SELECT count(DISTINCT b.id) FROM stu_ships b
                    WHERE a.id = b.starsystem_map_id
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ship_system ss
                                        WHERE b.id = ss.ship_id
                                        AND ss.system_type = :systemId
                                        AND ss.mode > 1)) AS shipcount,
                (SELECT count(DISTINCT c.id) FROM stu_ships c
                    WHERE a.id = c.starsystem_map_id
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_ship_system ss2
                                        WHERE c.id = ss2.ship_id
                                        AND ss2.system_type = :systemId
                                        AND ss2.mode > 1)) AS cloakcount,
                (SELECT COUNT(cfd) > 0
                    FROM stu_colonies col
                    JOIN stu_colonies_fielddata cfd
                    ON col.id = cfd.colonies_id
                    WHERE a.id = col.starsystem_map_id
                    AND cfd.aktiv = :active
                    AND cfd.buildings_id IN (
                        SELECT bf.buildings_id
                        FROM stu_buildings_functions bf
                        WHERE bf.function = :shieldBuilding)) AS shieldstate
                %s
                FROM stu_sys_map a
                LEFT JOIN stu_map_ftypes d ON d.id = a.field_id
                WHERE a.systems_id = :starSystemId AND a.sx BETWEEN :sxStart AND :sxEnd AND a.sy BETWEEN :syStart AND :syEnd
                GROUP BY a.sy, a.sx, a.id, d.type ORDER BY a.sy,a.sx',
                $doSubspace ? sprintf(self::FLIGHT_SIGNATURE_STAR_COUNT, $ignoreId, $maxAge) : ''
            ),
            $rsm
        )->setParameters([
            'starSystemId' => $system ? $system->getId() : $ship->getStarsystemMap()->getSystem()->getId(),
            'sxStart' => $system ? 1 : $map->getSx() - $sensorRange,
            'sxEnd' => $system ? $system->getMaxX() : $map->getSx() + $sensorRange,
            'syStart' => $system ? 1 : $map->getSy() - $sensorRange,
            'syEnd' => $system ? $system->getMaxY() : $map->getSy() + $sensorRange,
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK,
            'active' => 1,
            'shieldBuilding' => BuildingEnum::BUILDING_FUNCTION_SHIELD_GENERATOR
        ])->getResult();
    }

    private const FLIGHT_SIGNATURE_MAP_COUNT =
    ',(select count(distinct fs1.ship_id) from stu_flight_sig fs1
    where fs1.map_id = a.id
    AND fs1.user_id != %1$d
    AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
    AND fs1.time > %2$d) as d1c,
    (select count(distinct fs2.ship_id) from stu_flight_sig fs2
    where fs2.map_id = a.id
    AND fs2.user_id != %1$d
    AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
    AND fs2.time > %2$d) as d2c,
    (select count(distinct fs3.ship_id) from stu_flight_sig fs3
    where fs3.map_id = a.id
    AND fs3.user_id != %1$d
    AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
    AND fs3.time > %2$d) as d3c,
    (select count(distinct fs4.ship_id) from stu_flight_sig fs4
    where fs4.map_id = a.id
    AND fs4.user_id != %1$d
    AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
    AND fs4.time > %2$d) as d4c ';

    public function getSensorResultOuterSystem(int $cx, int $cy, int $layerId, int $sensorRange, bool $doSubspace, int $ignoreId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('cloakcount', 'cloakcount', 'integer');
        $rsm->addScalarResult('type', 'type', 'integer');
        $rsm->addScalarResult('allycolor', 'allycolor');
        $rsm->addScalarResult('usercolor', 'usercolor');
        $rsm->addScalarResult('factioncolor', 'factioncolor');

        if ($doSubspace) {
            $rsm->addScalarResult('d1c', 'd1c', 'integer');
            $rsm->addScalarResult('d2c', 'd2c', 'integer');
            $rsm->addScalarResult('d3c', 'd3c', 'integer');
            $rsm->addScalarResult('d4c', 'd4c', 'integer');

            $maxAge = time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED;
        }

        //TODO increase performance of allycolor/usercolor/factioncolor calculation
        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT a.id, a.cx AS posx,a.cy AS posy, d.type,
                (SELECT count(DISTINCT b.id) FROM stu_ships b
                    WHERE b.cx = a.cx AND b.cy = a.cy AND b.layer_id = a.layer_id
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ship_system ss
                                        WHERE b.id = ss.ship_id
                                        AND ss.system_type = :systemId
                                        AND ss.mode > 1)) AS shipcount,
                (SELECT count(DISTINCT c.id) FROM stu_ships c
                    WHERE c.map_id = a.id
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_ship_system ss2
                                        WHERE c.id = ss2.ship_id
                                        AND ss2.system_type = :systemId
                                        AND ss2.mode > 1)) AS cloakcount,
				(SELECT al.rgb_code FROM stu_alliances al
					JOIN stu_user u ON al.id = u.allys_id
						JOIN stu_ships s ON u.id = s.user_id
							JOIN stu_map m ON m.influence_area_id = s.influence_area_id
							WHERE m.id = a.id AND m.bordertype_id IS NULL AND m.admin_region_id IS NULL)
							AS allycolor,
				(SELECT u.rgb_code FROM stu_user u
					JOIN stu_ships s ON u.id = s.user_id
						JOIN stu_map m ON m.influence_area_id = s.influence_area_id
						WHERE m.id = a.id AND m.bordertype_id IS NULL AND m.admin_region_id IS NULL)
						as usercolor,
				(SELECT mb.color FROM stu_map_bordertypes mb
					JOIN stu_map m ON m.bordertype_id = mb.id
						WHERE m.id = a.id AND m.bordertype_id IS NOT NULL)
						AS factioncolor
                %s
                FROM stu_map a
                LEFT JOIN stu_map_ftypes d ON d.id = a.field_id
                WHERE a.cx BETWEEN :sxStart AND :sxEnd
                AND a.cy BETWEEN :syStart AND :syEnd
                AND a.layer_id = :layerId
                GROUP BY a.cy, a.cx, a.id, d.type, a.field_id ORDER BY a.cy, a.cx',
                $doSubspace ? sprintf(self::FLIGHT_SIGNATURE_MAP_COUNT, $ignoreId, $maxAge) : ''
            ),
            $rsm
        )->setParameters([
            'sxStart' => $cx - $sensorRange,
            'sxEnd' => $cx + $sensorRange,
            'syStart' => $cy - $sensorRange,
            'syEnd' => $cy + $sensorRange,
            'layerId' => $layerId,
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
        ])->getResult();
    }

    private const ADMIN_SIGNATURE_MAP_COUNT_USER =
    ',(select count(distinct fs1.ship_id) from stu_flight_sig fs1
    where fs1.map_id = a.id
    AND fs1.user_id = %1$d
    AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
    AND fs1.time > %2$d) as d1c,
    (select count(distinct fs2.ship_id) from stu_flight_sig fs2
    where fs2.map_id = a.id
    AND fs2.user_id = %1$d
    AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
    AND fs2.time > %2$d) as d2c,
    (select count(distinct fs3.ship_id) from stu_flight_sig fs3
    where fs3.map_id = a.id
    AND fs3.user_id = %1$d
    AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
    AND fs3.time > %2$d) as d3c,
    (select count(distinct fs4.ship_id) from stu_flight_sig fs4
    where fs4.map_id = a.id
    AND fs4.user_id = %1$d
    AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
    AND fs4.time > %2$d) as d4c ';

    public function getSignaturesOuterSystemOfUser(int $minx, int $maxx, int $miny, int $maxy, int $layerId, int $userId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('type', 'type', 'integer');

        $rsm->addScalarResult('d1c', 'd1c', 'integer');
        $rsm->addScalarResult('d2c', 'd2c', 'integer');
        $rsm->addScalarResult('d3c', 'd3c', 'integer');
        $rsm->addScalarResult('d4c', 'd4c', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT a.id, a.cx as posx,a.cy as posy, d.type,
                    (SELECT count(distinct b.id)
                        FROM stu_ships b
                        WHERE b.cx = a.cx AND b.cy = a.cy AND b.layer_id = a.layer_id
                        AND b.user_id = :userId) as shipcount
                %s
                FROM stu_map a
                LEFT JOIN stu_map_ftypes d ON d.id = a.field_id
                WHERE a.cx BETWEEN :sxStart AND :sxEnd
                AND a.cy BETWEEN :syStart AND :syEnd
                AND a.layer_id = :layerId
                GROUP BY a.cy, a.cx, a.id, d.type, a.field_id ORDER BY a.cy, a.cx',
                sprintf(self::ADMIN_SIGNATURE_MAP_COUNT_USER, $userId, 0)
            ),
            $rsm
        )->setParameters([
            'sxStart' => $minx,
            'sxEnd' => $maxx,
            'syStart' => $miny,
            'syEnd' => $maxy,
            'layerId' => $layerId,
            'userId' => $userId
        ])->getResult();
    }

    private const ADMIN_SIGNATURE_MAP_COUNT_ALLY =
    ',(select count(distinct fs1.ship_id) from stu_flight_sig fs1
    JOIN stu_user u1 ON fs1.user_id = u1.id
    WHERE fs1.map_id = a.id
    AND u1.allys_id = %1$d
    AND (fs1.from_direction = 1 OR fs1.to_direction = 1)
    AND fs1.time > %2$d) as d1c,
    (select count(distinct fs2.ship_id) from stu_flight_sig fs2
    JOIN stu_user u2 ON fs2.user_id = u2.id
    WHERE fs2.map_id = a.id
    AND u2.allys_id = %1$d
    AND (fs2.from_direction = 2 OR fs2.to_direction = 2)
    AND fs2.time > %2$d) as d2c,
    (select count(distinct fs3.ship_id) from stu_flight_sig fs3
    JOIN stu_user u3 ON fs3.user_id = u3.id
    WHERE fs3.map_id = a.id
    AND u3.allys_id = %1$d
    AND (fs3.from_direction = 3 OR fs3.to_direction = 3)
    AND fs3.time > %2$d) as d3c,
    (select count(distinct fs4.ship_id) from stu_flight_sig fs4
    JOIN stu_user u4 ON fs4.user_id = u4.id
    WHERE fs4.map_id = a.id
    AND u4.allys_id = %1$d
    AND (fs4.from_direction = 4 OR fs4.to_direction = 4)
    AND fs4.time > %2$d) as d4c ';

    public function getSignaturesOuterSystemOfAlly(int $minx, int $maxx, int $miny, int $maxy, int $layerId, int $allyId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('type', 'type', 'integer');

        $rsm->addScalarResult('d1c', 'd1c', 'integer');
        $rsm->addScalarResult('d2c', 'd2c', 'integer');
        $rsm->addScalarResult('d3c', 'd3c', 'integer');
        $rsm->addScalarResult('d4c', 'd4c', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT a.id, a.cx as posx,a.cy as posy, d.type,
                    (SELECT count(distinct b.id)
                        FROM stu_ships b
                        JOIN stu_user u ON b.user_id = u.id
                        WHERE b.cx = a.cx AND b.cy = a.cy AND b.layer_id = a.layer_id
                        AND u.allys_id = :allyId) as shipcount
                %s
                FROM stu_map a
                LEFT JOIN stu_map_ftypes d ON d.id = a.field_id
                WHERE a.cx BETWEEN :sxStart AND :sxEnd
                AND a.cy BETWEEN :syStart AND :syEnd
                AND a.layer_id = :layerId
                GROUP BY a.cy, a.cx, a.id, d.type, a.field_id ORDER BY a.cy, a.cx',
                sprintf(self::ADMIN_SIGNATURE_MAP_COUNT_ALLY, $allyId, 0)
            ),
            $rsm
        )->setParameters([
            'sxStart' => $minx,
            'sxEnd' => $maxx,
            'syStart' => $miny,
            'syEnd' => $maxy,
            'layerId' => $layerId,
            'allyId' => $allyId
        ])->getResult();
    }

    public function getFleetShipsScannerResults(
        ShipInterface $ship,
        bool $showCloaked = false,
        int $mapId = null,
        int $sysMapId = null
    ): iterable {
        $isSystem = $sysMapId !== null || ($mapId === null && $ship->getSystem() !== null);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('fleetid', 'fleetid', 'integer');
        $rsm->addScalarResult('fleetname', 'fleetname');
        $rsm->addScalarResult('isdefending', 'isdefending', 'boolean');
        $rsm->addScalarResult('isblocking', 'isblocking', 'boolean');
        $rsm->addScalarResult('shipid', 'shipid', 'integer');
        $rsm->addScalarResult('rumpid', 'rumpid', 'integer');
        $rsm->addScalarResult('formerrumpid', 'formerrumpid', 'integer');
        $rsm->addScalarResult('warpstate', 'warpstate', 'integer');
        $rsm->addScalarResult('cloakstate', 'cloakstate', 'integer');
        $rsm->addScalarResult('shieldstate', 'shieldstate', 'integer');
        $rsm->addScalarResult('uplinkstate', 'uplinkstate', 'integer');
        $rsm->addScalarResult('isdestroyed', 'isdestroyed', 'boolean');
        $rsm->addScalarResult('spacecrafttype', 'spacecrafttype', 'integer');
        $rsm->addScalarResult('shipname', 'shipname');
        $rsm->addScalarResult('hull', 'hull', 'integer');
        $rsm->addScalarResult('maxhull', 'maxhull', 'integer');
        $rsm->addScalarResult('shield', 'shield', 'integer');
        $rsm->addScalarResult('webid', 'webid', 'integer');
        $rsm->addScalarResult('webfinishtime', 'webfinishtime', 'integer');
        $rsm->addScalarResult('userid', 'userid', 'integer');
        $rsm->addScalarResult('username', 'username');
        $rsm->addScalarResult('rumpcategoryid', 'rumpcategoryid', 'integer');
        $rsm->addScalarResult('rumpname', 'rumpname');
        $rsm->addScalarResult('rumproleid', 'rumproleid', 'integer');
        $rsm->addScalarResult('haslogbook', 'haslogbook', 'boolean');

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT f.id as fleetid, f.name as fleetname, f.defended_colony_id is not null as isdefending,
                    f.blocked_colony_id is not null as isblocking, s.id as shipid, s.rumps_id as rumpid, s.former_rumps_id as formerrumpid,
                    ss.mode as warpstate, COALESCE(ss2.mode,0) as cloakstate, ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate, s.is_destroyed as isdestroyed,
                    s.type as spacecrafttype, s.name as shipname, s.huelle as hull, s.max_huelle as maxhull, s.schilde as shield, s.holding_web_id as webid, tw.finished_time as webfinishtime,
                    u.id as userid, u.username, r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_ship_log sl WHERE sl.ship_id = s.id AND sl.is_private = false) as haslogbook
                FROM stu_ships s
                LEFT JOIN stu_ship_system ss
                ON s.id = ss.ship_id
                AND ss.system_type = :warpdriveType
                LEFT JOIN stu_ship_system ss2
                ON s.id = ss2.ship_id
                AND ss2.system_type = :cloakType
                LEFT JOIN stu_ship_system ss3
                ON s.id = ss3.ship_id
                AND ss3.system_type = :shieldType
                LEFT JOIN stu_ship_system ss4
                ON s.id = ss4.ship_id
                AND ss4.system_type = :uplinkType
                JOIN stu_rumps r
                ON s.rumps_id = r.id
                JOIN stu_fleets f
                ON s.fleets_id = f.id
                LEFT OUTER JOIN stu_tholian_web tw
                ON s.holding_web_id = tw.id
                JOIN stu_user u
                ON s.user_id = u.id
                WHERE s.%s = :fieldId
                AND s.id != :ignoreId
                %s
                ORDER BY f.sort DESC, f.id DESC, (CASE WHEN s.is_fleet_leader THEN 0 ELSE 1 END), r.category_id ASC, r.role_id ASC, r.id ASC, s.name ASC',
                $isSystem ? 'starsystem_map_id' : 'map_id',
                $showCloaked ? '' : sprintf(' AND (s.user_id = %d OR COALESCE(ss2.mode,0) < %d) ', $ship->getUser()->getId(), ShipSystemModeEnum::MODE_ON)
            ),
            $rsm
        )->setParameters([
            'fieldId' => $mapId ?? $sysMapId ?? ($isSystem ? $ship->getStarsystemMap()->getId() : $ship->getMap()->getId()),
            'ignoreId' => $ship->getId(),
            'cloakType' => ShipSystemTypeEnum::SYSTEM_CLOAK,
            'warpdriveType' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            'shieldType' => ShipSystemTypeEnum::SYSTEM_SHIELDS,
            'uplinkType' => ShipSystemTypeEnum::SYSTEM_UPLINK
        ])->getResult();
    }

    public function getSingleShipScannerResults(
        ShipInterface $ship,
        array $types,
        bool $showCloaked = false,
        int $mapId = null,
        int $sysMapId = null
    ): iterable {
        $isSystem = $sysMapId !== null || ($mapId === null && $ship->getSystem() !== null);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('shipid', 'shipid', 'integer');
        $rsm->addScalarResult('fleetid', 'fleetid', 'integer');
        $rsm->addScalarResult('rumpid', 'rumpid', 'integer');
        $rsm->addScalarResult('formerrumpid', 'formerrumpid', 'integer');
        $rsm->addScalarResult('warpstate', 'warpstate', 'integer');
        $rsm->addScalarResult('cloakstate', 'cloakstate', 'integer');
        $rsm->addScalarResult('shieldstate', 'shieldstate', 'integer');
        $rsm->addScalarResult('uplinkstate', 'uplinkstate', 'integer');
        $rsm->addScalarResult('isdestroyed', 'isdestroyed', 'boolean');
        $rsm->addScalarResult('spacecrafttype', 'spacecrafttype', 'integer');
        $rsm->addScalarResult('shipname', 'shipname', 'string');
        $rsm->addScalarResult('hull', 'hull', 'integer');
        $rsm->addScalarResult('maxhull', 'maxhull', 'integer');
        $rsm->addScalarResult('shield', 'shield', 'integer');
        $rsm->addScalarResult('webid', 'webid', 'integer');
        $rsm->addScalarResult('webfinishtime', 'webfinishtime', 'integer');
        $rsm->addScalarResult('userid', 'userid', 'integer');
        $rsm->addScalarResult('username', 'username', 'string');
        $rsm->addScalarResult('rumpcategoryid', 'rumpcategoryid', 'integer');
        $rsm->addScalarResult('rumpname', 'rumpname', 'string');
        $rsm->addScalarResult('rumproleid', 'rumproleid', 'integer');
        $rsm->addScalarResult('haslogbook', 'haslogbook', 'boolean');

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT s.id as shipid, s.fleets_id as fleetid, s.rumps_id as rumpid , s.former_rumps_id as formerrumpid, ss.mode as warpstate, COALESCE(ss2.mode,0) as cloakstate,
                    ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate, s.is_destroyed as isdestroyed, s.type as spacecrafttype, s.name as shipname,
                    s.huelle as hull, s.max_huelle as maxhull, s.schilde as shield, s.holding_web_id as webid, tw.finished_time as webfinishtime, u.id as userid, u.username,
                    r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_ship_log sl WHERE sl.ship_id = s.id AND sl.is_private = false) as haslogbook
                FROM stu_ships s
                LEFT JOIN stu_ship_system ss
                ON s.id = ss.ship_id
                AND ss.system_type = :warpdriveType
                LEFT JOIN stu_ship_system ss2
                ON s.id = ss2.ship_id
                AND ss2.system_type = :cloakType
                LEFT JOIN stu_ship_system ss3
                ON s.id = ss3.ship_id
                AND ss3.system_type = :shieldType
                LEFT JOIN stu_ship_system ss4
                ON s.id = ss4.ship_id
                AND ss4.system_type = :uplinkType
                JOIN stu_rumps r
                ON s.rumps_id = r.id
                LEFT OUTER JOIN stu_tholian_web tw
                ON s.holding_web_id = tw.id
                JOIN stu_user u
                ON s.user_id = u.id
                WHERE s.%s = :fieldId
                AND s.id != :ignoreId
                AND s.fleets_id IS NULL
                AND s.type IN (:types)
                %s
                ORDER BY r.category_id ASC, r.role_id ASC, r.id ASC, s.name ASC',
                $isSystem ? 'starsystem_map_id' : 'map_id',
                $showCloaked ? '' : sprintf(' AND (s.user_id = %d OR COALESCE(ss2.mode,0) < %d) ', $ship->getUser()->getId(), ShipSystemModeEnum::MODE_ON)
            ),
            $rsm
        )->setParameters([
            'fieldId' => $mapId ?? $sysMapId ?? ($isSystem ? $ship->getStarsystemMap()->getId() : $ship->getMap()->getId()),
            'ignoreId' => $ship->getId(),
            'types' => $types,
            'cloakType' => ShipSystemTypeEnum::SYSTEM_CLOAK,
            'warpdriveType' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            'shieldType' => ShipSystemTypeEnum::SYSTEM_SHIELDS,
            'uplinkType' => ShipSystemTypeEnum::SYSTEM_UPLINK
        ])->getResult();
    }

    public function isCloakedShipAtShipLocation(
        ShipInterface $ship
    ): bool {
        return $this->isCloakedShipAtLocation(
            $ship->getStarsystemMap()->getId(),
            $ship->getMap()->getId(),
            $ship->getUser()->getId()
        );
    }

    public function isCloakedShipAtLocation(
        ?int $sysMapId,
        ?int $mapId,
        int $ignoreId
    ): bool {
        $cloakSql = sprintf(
            ' AND EXISTS (SELECT ss.id
                            FROM %s ss
                            WHERE s.id = ss.ship_id
                            AND ss.system_type = %d
                            AND ss.mode > 1) ',
            ShipSystem::class,
            ShipSystemTypeEnum::SYSTEM_CLOAK
        );

        $result = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(s.id) FROM %s s
                    WHERE s.%s = :fieldId
                    %s
                    AND s.user_id != :ignoreId',
                Ship::class,
                $sysMapId !== null ? 'starsystem_map_id' : 'map_id',
                $cloakSql
            )
        )->setParameters([
            'fieldId' => $mapId ?? $sysMapId,
            'ignoreId' => $ignoreId
        ])->getSingleScalarResult();

        return $result > 0;
    }

    public function getRandomShipIdWithCrewByUser(int $userId): ?int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $result = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT s.id as id FROM stu_ships s
                WHERE s.user_id = :userId
                AND EXISTS (SELECT sc.id
                            FROM stu_crew_assign sc
                            WHERE s.id = sc.ship_id)
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

    public function isBaseOnLocation(ShipInterface $ship): bool
    {
        $isSystem = $ship->getSystem() !== null;

        $query = $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(s.id) FROM %s s
                WHERE s.%s = :mapId
                AND s.type = :type',
                Ship::class,
                $isSystem ? 'starsystem_map_id' : 'map_id',
            )
        )->setParameters([
            'mapId' => $isSystem ? $ship->getStarsystemMap()->getId() : $ship->getMap()->getId(),
            'type' => SpacecraftTypeEnum::SPACECRAFT_TYPE_STATION
        ]);

        return $query->getSingleScalarResult() > 0;
    }

    public function getStationsByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s
                    FROM %s s
                    JOIN %s r
                    WITH s.rumps_id = r.id
                    WHERE s.user_id = :userId
                    AND r.category_id = :categoryId',
                    Ship::class,
                    ShipRump::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'categoryId' => ShipRumpEnum::SHIP_CATEGORY_STATION
            ])
            ->getResult();
    }

    public function getAllDockedShips(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.dock IS NOT NULL',
                Ship::class
            )
        )->getResult();
    }

    public function getAllTractoringShips(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.tractored_ship_id IS NOT NULL',
                Ship::class
            )
        )->getResult();
    }

    public function truncateAllShips(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s s',
                Ship::class
            )
        )->execute();
    }
}
