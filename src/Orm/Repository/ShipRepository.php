<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipCrew;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipSystem;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\ShipStorage;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserInterface;

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
        $em->flush();
    }

    public function getAmountByUserAndSpecialAbility(
        int $userId,
        int $specialAbilityId
    ): int {
        return (int) $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT COUNT(s) FROM %s s WHERE s.user_id = :userId AND s.rumps_id IN (
                    SELECT rs.rumps_id FROM %s rs WHERE rs.special = :specialAbilityId
                )',
                Ship::class,
                ShipRumpSpecial::class
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

    public function getByUserAndRump(int $userId, int $rumpId): iterable
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
                AND s.is_base = false',
                Ship::class,
                $isSystem ? 'starsystem_map_id' : 'map_id'
            )
        )->setParameters([
            'userId' => $fleetLeader->getUser()->getId(),
            'mapId' => $isSystem ? $fleetLeader->getStarsystemMap()->getId() : $fleetLeader->getMap()->getId()
        ])->getResult();
    }

    public function getByInnerSystemLocation(
        int $starSystemId,
        int $sx,
        int $sy
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s sm
                WITH s.starsystem_map_id = sm.id
                WHERE sm.systems_id = :starSystemId
                AND sm.sx = :sx AND sm.sy = :sy
                AND NOT EXISTS (SELECT ss.id
                                    FROM %s ss
                                    WHERE s.id = ss.ships_id
                                    AND ss.system_type = :systemId
                                    AND ss.mode > 1)
                ORDER BY s.is_destroyed ASC, s.fleets_id DESC, s.id ASC',
                Ship::class,
                StarSystemMap::class,
                ShipSystem::class
            )
        )->setParameters([
            'starSystemId' => $starSystemId,
            'sx' => $sx,
            'sy' => $sy,
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
        ])->getResult();
    }

    public function getByOuterSystemLocation(
        int $cx,
        int $cy
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.starsystem_map_id is null AND s.cx = :cx AND s.cy = :cy
                AND NOT EXISTS (SELECT ss.id
                                    FROM %s ss
                                    WHERE s.id = ss.ships_id
                                    AND ss.system_type = :systemId
                                    AND ss.mode > 1)
                ORDER BY s.is_destroyed ASC, s.fleets_id DESC, s.id ASC',
                Ship::class,
                ShipSystem::class
            )
        )->setParameters([
            'cx' => $cx,
            'cy' => $cy,
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
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

    public function getByUserAndFleetAndBase(int $userId, ?int $fleetId, bool $isBase): iterable
    {
        return $this->findBy(
            [
                'user_id' => $userId,
                'fleets_id' => $fleetId,
                'is_base' => $isBase,
            ],
            $isBase ? ['max_huelle' => 'desc', 'id' => 'asc'] : ['id' => 'asc']
        );
    }

    public function getByUplink(int $userId): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s sc
                WITH s.id = sc.ships_id
                JOIN %s c
                WITH sc.crew_id = c.id
                JOIN %s ss
                WITH ss.ships_id = s.id
                JOIN %s u
                WITH s.user_id = u.id
                WHERE s.user_id != :userId
                AND c.user_id = :userId
                AND ss.system_type = :systemType
                AND ss.mode >= :mode
                AND u.vac_active = false',
                Ship::class,
                ShipCrew::class,
                Crew::class,
                ShipSystem::class,
                User::class
            )
        )->setParameters([
            'userId' => $userId,
            'systemType' => ShipSystemTypeEnum::SYSTEM_UPLINK,
            'mode' => ShipSystemModeEnum::MODE_ON
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
                    SELECT ss.ships_id FROM %s ss WHERE ss.goods_id = :commodityId AND ss.count >= :amount
                )',
                Ship::class,
                ShipStorage::class
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
        //TODO join with shield ship system and check for state = off
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.is_destroyed = :destroyedState AND s.schilde<s.max_schilde AND s.shield_regeneration_timer <= :regenerationThreshold',
                Ship::class
            )
        )->setParameters([
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
                WITH sc.ships_id = s.id
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
                WHERE s.user_id > 100
                AND r.category_id = :catId',
                Ship::class,
                ShipRump::class
            )
        )->setParameter('catId', ShipRumpEnum::SHIP_CATEGORY_CONSTRUCTION)
            ->getResult();
    }

    public function getPlayerShipsForTick(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.user_id > 100 AND s.plans_id > 0',
                Ship::class
            )
        )->getResult();
    }

    public function getNpcShipsForTick(): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.user_id BETWEEN 2 AND 100',
                Ship::class
            )
        )->getResult();
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

    public function getSensorResultInnerSystem(ShipInterface $ship, StarSystemInterface $system = null): iterable
    {
        $doSubspace = $ship->getSubspaceState();
        $map = $ship->getStarsystemMap();
        $sensorRange = $ship->getSensorRange();
        $ignoreId = $ship->getUser()->getId();

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('cloakcount', 'cloakcount', 'integer');
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
                'SELECT a.id, a.sx as posx,a.sy as posy, d.type,
                (select count(distinct b.id)from stu_ships b
                    where a.id = b.starsystem_map_id
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ships_systems ss
                                        WHERE b.id = ss.ships_id
                                        AND ss.system_type = :systemId
                                        AND ss.mode > 1)) as shipcount,
                (select count(distinct c.id) from stu_ships c
                    where a.id = c.starsystem_map_id
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_ships_systems ss2
                                        WHERE c.id = ss2.ships_id
                                        AND ss2.system_type = :systemId
                                        AND ss2.mode > 1)) as cloakcount
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
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
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

    public function getSensorResultOuterSystem(int $cx, int $cy, int $sensorRange, bool $doSubspace, $ignoreId): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('cloakcount', 'cloakcount', 'integer');
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
                'SELECT a.id, a.cx as posx,a.cy as posy, d.type,
                (select count(distinct b.id)from stu_ships b
                    where b.cx=a.cx AND b.cy=a.cy
                    AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ships_systems ss
                                        WHERE b.id = ss.ships_id
                                        AND ss.system_type = :systemId
                                        AND ss.mode > 1)) as shipcount,
                (select count(distinct c.id) from stu_ships c
                    where c.cx = a.cx AND c.cy=a.cy
                    AND EXISTS (SELECT ss2.id
                                        FROM stu_ships_systems ss2
                                        WHERE c.id = ss2.ships_id
                                        AND ss2.system_type = :systemId
                                        AND ss2.mode > 1)) as cloakcount
                %s 
                FROM stu_map a
                LEFT JOIN stu_map_ftypes d ON d.id = a.field_id
                WHERE a.cx BETWEEN :sxStart AND :sxEnd AND a.cy BETWEEN :syStart AND :syEnd 
                GROUP BY a.cy, a.cx, a.id, d.type, a.field_id ORDER BY a.cy, a.cx',
                $doSubspace ? sprintf(self::FLIGHT_SIGNATURE_MAP_COUNT, $ignoreId, $maxAge) : ''
            ),
            $rsm
        )->setParameters([
            'sxStart' => $cx - $sensorRange,
            'sxEnd' => $cx + $sensorRange,
            'syStart' => $cy - $sensorRange,
            'syEnd' => $cy + $sensorRange,
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
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
        $rsm->addScalarResult('fleetname', 'fleetname', 'string');
        $rsm->addScalarResult('isdefending', 'isdefending', 'boolean');
        $rsm->addScalarResult('isblocking', 'isblocking', 'boolean');
        $rsm->addScalarResult('shipid', 'shipid', 'integer');
        $rsm->addScalarResult('rumpid', 'rumpid', 'integer');
        $rsm->addScalarResult('warpstate', 'warpstate', 'integer');
        $rsm->addScalarResult('cloakstate', 'cloakstate', 'integer');
        $rsm->addScalarResult('shieldstate', 'shieldstate', 'integer');
        $rsm->addScalarResult('uplinkstate', 'uplinkstate', 'integer');
        $rsm->addScalarResult('isdestroyed', 'isdestroyed', 'boolean');
        $rsm->addScalarResult('isbase', 'isbase', 'boolean');
        $rsm->addScalarResult('shipname', 'shipname', 'string');
        $rsm->addScalarResult('hull', 'hull', 'integer');
        $rsm->addScalarResult('maxhull', 'maxhull', 'integer');
        $rsm->addScalarResult('shield', 'shield', 'integer');
        $rsm->addScalarResult('userid', 'userid', 'integer');
        $rsm->addScalarResult('username', 'username', 'string');
        $rsm->addScalarResult('rumpcategoryid', 'rumpcategoryid', 'integer');
        $rsm->addScalarResult('rumpname', 'rumpname', 'string');

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT f.id as fleetid, f.name as fleetname, f.defended_colony_id is not null as isdefending,
                    f.blocked_colony_id is not null as isblocking, s.id as shipid, s.rumps_id as rumpid,
                    ss.mode as warpstate, COALESCE(ss2.mode,0) as cloakstate, ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate, s.is_destroyed as isdestroyed,
                    s.is_base as isbase, s.name as shipname, s.huelle as hull, s.max_huelle as maxhull, s.schilde as shield,
                    u.id as userid, u.username, r.category_id as rumpcategoryid, r.name as rumpname
                FROM stu_ships s
                LEFT JOIN stu_ships_systems ss
                ON s.id = ss.ships_id
                AND ss.system_type = :warpdriveType
                LEFT JOIN stu_ships_systems ss2
                ON s.id = ss2.ships_id
                AND ss2.system_type = :cloakType
                LEFT JOIN stu_ships_systems ss3
                ON s.id = ss3.ships_id
                AND ss3.system_type = :shieldType
                LEFT JOIN stu_ships_systems ss4
                ON s.id = ss4.ships_id
                AND ss4.system_type = :uplinkType
                JOIN stu_rumps r
                ON s.rumps_id = r.id
                JOIN stu_fleets f
                ON s.fleets_id = f.id
                JOIN stu_user u
                ON s.user_id = u.id
                WHERE s.%s = :fieldId
                AND s.id != :ignoreId
                %s
                ORDER BY f.sort desc, f.id desc, (case when s.is_fleet_leader then 0 else 1 end)',
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
        bool $isBase,
        bool $showCloaked = false,
        int $mapId = null,
        int $sysMapId = null
    ): iterable {

        $isSystem = $sysMapId !== null || ($mapId === null && $ship->getSystem() !== null);

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('shipid', 'shipid', 'integer');
        $rsm->addScalarResult('rumpid', 'rumpid', 'integer');
        $rsm->addScalarResult('warpstate', 'warpstate', 'integer');
        $rsm->addScalarResult('cloakstate', 'cloakstate', 'integer');
        $rsm->addScalarResult('shieldstate', 'shieldstate', 'integer');
        $rsm->addScalarResult('uplinkstate', 'uplinkstate', 'integer');
        $rsm->addScalarResult('isdestroyed', 'isdestroyed', 'boolean');
        $rsm->addScalarResult('isbase', 'isbase', 'boolean');
        $rsm->addScalarResult('shipname', 'shipname', 'string');
        $rsm->addScalarResult('hull', 'hull', 'integer');
        $rsm->addScalarResult('maxhull', 'maxhull', 'integer');
        $rsm->addScalarResult('shield', 'shield', 'integer');
        $rsm->addScalarResult('userid', 'userid', 'integer');
        $rsm->addScalarResult('username', 'username', 'string');
        $rsm->addScalarResult('rumpcategoryid', 'rumpcategoryid', 'integer');
        $rsm->addScalarResult('rumpname', 'rumpname', 'string');

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT s.id as shipid, s.rumps_id as rumpid , ss.mode as warpstate, COALESCE(ss2.mode,0) as cloakstate,
                    ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate, s.is_destroyed as isdestroyed, s.is_base as isbase, s.name as shipname,
                    s.huelle as hull, s.max_huelle as maxhull, s.schilde as shield, u.id as userid, u.username,
                    r.category_id as rumpcategoryid, r.name as rumpname
                FROM stu_ships s
                LEFT JOIN stu_ships_systems ss
                ON s.id = ss.ships_id
                AND ss.system_type = :warpdriveType
                LEFT JOIN stu_ships_systems ss2
                ON s.id = ss2.ships_id
                AND ss2.system_type = :cloakType
                LEFT JOIN stu_ships_systems ss3
                ON s.id = ss3.ships_id
                AND ss3.system_type = :shieldType
                LEFT JOIN stu_ships_systems ss4
                ON s.id = ss4.ships_id
                AND ss4.system_type = :uplinkType
                JOIN stu_rumps r
                ON s.rumps_id = r.id
                JOIN stu_user u
                ON s.user_id = u.id
                WHERE s.%s = :fieldId
                AND s.id != :ignoreId
                AND s.fleets_id IS NULL
                AND s.is_base = :isBase
                %s',
                $isSystem ? 'starsystem_map_id' : 'map_id',
                $showCloaked ? '' : sprintf(' AND (s.user_id = %d OR COALESCE(ss2.mode,0) < %d) ', $ship->getUser()->getId(), ShipSystemModeEnum::MODE_ON)
            ),
            $rsm
        )->setParameters([
            'fieldId' => $mapId ?? $sysMapId ?? ($isSystem ? $ship->getStarsystemMap()->getId() : $ship->getMap()->getId()),
            'ignoreId' => $ship->getId(),
            'isBase' => $isBase,
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
        int $sysMapId,
        int $mapId,
        int $ignoreId
    ): bool {

        $cloakSql = sprintf(
            ' AND EXISTS (SELECT ss.id
                            FROM %s ss
                            WHERE s.id = ss.ships_id
                            AND ss.system_type = %d
                            AND ss.mode > 1) ',
            ShipSystem::class,
            ShipSystemTypeEnum::SYSTEM_CLOAK
        );

        return $this->getEntityManager()->createQuery(
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
        ])->getSingleScalarResult() > 0;
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
                            FROM stu_ships_crew sc
                            WHERE s.id = sc.ships_id) 
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
                AND s.is_base = true',
                Ship::class,
                $isSystem ? 'starsystem_map_id' : 'map_id',
            )
        )->setParameters([
            'mapId' => $isSystem  ? $ship->getStarsystemMap()->getId() : $ship->getMap()->getId()
        ]);

        return $query->getSingleScalarResult() > 0;
    }
}
