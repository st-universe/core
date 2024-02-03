<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipRumpSpecialAbilityEnum;
use Stu\Module\Ship\Lib\TFleetShipItem;
use Stu\Module\Ship\Lib\TShipItem;
use Stu\Orm\Entity\Anomaly;
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
                'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK->value
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
                'ignoreIds' => [$ship->getUser()->getId(), UserEnum::USER_NOONE],
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
            'cloakSystemId' => ShipSystemTypeEnum::SYSTEM_CLOAK->value,
            'warpSystemId' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE->value,
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
            'systemType' => ShipSystemTypeEnum::SYSTEM_UPLINK->value,
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
                AND (SELECT count(sc.id) FROM %s sc WHERE s.id = sc.ship_id) >= bp.crew
                AND NOT EXISTS (SELECT a FROM %s a WHERE (a.map_id = s.map_id or a.starsystem_map_id = s.starsystem_map_id) AND a.anomaly_type_id = :anomalyType AND a.remaining_ticks > 0)',
                Ship::class,
                ShipSystem::class,
                ShipBuildplan::class,
                ShipCrew::class,
                Anomaly::class
            )
        )->setParameters([
            'shieldType' => ShipSystemTypeEnum::SYSTEM_SHIELDS->value,
            'modeOn' => ShipSystemModeEnum::MODE_ON,
            'regenerationThreshold' => $regenerationThreshold,
            'destroyedState' => 0,
            'anomalyType' => AnomalyTypeEnum::SUBSPACE_ELLIPSE
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

    public function getFleetShipsScannerResults(
        ShipInterface $ship,
        bool $showCloaked = false,
        int $mapId = null,
        int $sysMapId = null
    ): array {
        $isSystem = $sysMapId !== null || ($mapId === null && $ship->getSystem() !== null);

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TFleetShipItem::class, 's');
        $rsm->addFieldResult('s', 'fleetname', 'fleet_name');
        $rsm->addFieldResult('s', 'isdefending', 'is_defending');
        $rsm->addFieldResult('s', 'isblocking', 'is_blocking');
        $this->addTShipItemFields($rsm);

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT f.id as fleetid, f.name as fleetname, f.defended_colony_id is not null as isdefending,
                    f.blocked_colony_id is not null as isblocking, s.id as shipid, s.rumps_id as rumpid, s.former_rumps_id as formerrumpid,
                    ss.mode as warpstate, COALESCE(ss2.mode,0) as cloakstate, ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate, s.is_destroyed as isdestroyed,
                    s.type as spacecrafttype, s.name as shipname, s.huelle as hull, s.max_huelle as maxhull, s.schilde as shield, s.holding_web_id as webid, tw.finished_time as webfinishtime,
                    u.id as userid, u.username, r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_ship_log sl WHERE sl.ship_id = s.id AND sl.is_private = false) as haslogbook,
                    (SELECT count(*) > 0 FROM stu_crew_assign ca WHERE ca.ship_id = s.id) as hascrew
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
            'cloakType' => ShipSystemTypeEnum::SYSTEM_CLOAK->value,
            'warpdriveType' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE->value,
            'shieldType' => ShipSystemTypeEnum::SYSTEM_SHIELDS->value,
            'uplinkType' => ShipSystemTypeEnum::SYSTEM_UPLINK->value
        ])->getResult();
    }

    public function getSingleShipScannerResults(
        ShipInterface $ship,
        array $types,
        bool $showCloaked = false,
        int $mapId = null,
        int $sysMapId = null
    ): array {
        $isSystem = $sysMapId !== null || ($mapId === null && $ship->getSystem() !== null);

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TShipItem::class, 's');
        $this->addTShipItemFields($rsm);

        return $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT s.id as shipid, s.fleets_id as fleetid, s.rumps_id as rumpid , s.former_rumps_id as formerrumpid, ss.mode as warpstate, COALESCE(ss2.mode,0) as cloakstate,
                    ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate, s.is_destroyed as isdestroyed, s.type as spacecrafttype, s.name as shipname,
                    s.huelle as hull, s.max_huelle as maxhull, s.schilde as shield, s.holding_web_id as webid, tw.finished_time as webfinishtime, u.id as userid, u.username,
                    r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_ship_log sl WHERE sl.ship_id = s.id AND sl.is_private = false) as haslogbook,
                    (SELECT count(*) > 0 FROM stu_crew_assign ca WHERE ca.ship_id = s.id) as hascrew
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
            'cloakType' => ShipSystemTypeEnum::SYSTEM_CLOAK->value,
            'warpdriveType' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE->value,
            'shieldType' => ShipSystemTypeEnum::SYSTEM_SHIELDS->value,
            'uplinkType' => ShipSystemTypeEnum::SYSTEM_UPLINK->value
        ])->getResult();
    }

    private function addTShipItemFields(ResultSetMapping $rsm): void
    {
        $rsm->addFieldResult('s', 'shipid', 'ship_id');
        $rsm->addFieldResult('s', 'fleetid', 'fleet_id');
        $rsm->addFieldResult('s', 'rumpid', 'rump_id');
        $rsm->addFieldResult('s', 'formerrumpid', 'former_rump_id');
        $rsm->addFieldResult('s', 'warpstate', 'warp_state');
        $rsm->addFieldResult('s', 'cloakstate', 'cloak_state');
        $rsm->addFieldResult('s', 'shieldstate', 'shield_state');
        $rsm->addFieldResult('s', 'uplinkstate', 'uplink_state');
        $rsm->addFieldResult('s', 'isdestroyed', 'is_destroyed');
        $rsm->addFieldResult('s', 'spacecrafttype', 'spacecraft_type');
        $rsm->addFieldResult('s', 'shipname', 'ship_name');
        $rsm->addFieldResult('s', 'hull', 'hull');
        $rsm->addFieldResult('s', 'maxhull', 'max_hull');
        $rsm->addFieldResult('s', 'shield', 'shield');
        $rsm->addFieldResult('s', 'webid', 'web_id');
        $rsm->addFieldResult('s', 'webfinishtime', 'web_finish_time');
        $rsm->addFieldResult('s', 'userid', 'user_id');
        $rsm->addFieldResult('s', 'username', 'user_name');
        $rsm->addFieldResult('s', 'rumpcategoryid', 'rump_category_id');
        $rsm->addFieldResult('s', 'rumpname', 'rump_name');
        $rsm->addFieldResult('s', 'rumproleid', 'rump_role_id');
        $rsm->addFieldResult('s', 'haslogbook', 'has_logbook');
        $rsm->addFieldResult('s', 'hascrew', 'has_crew');
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
            ShipSystemTypeEnum::SYSTEM_CLOAK->value
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

    public function getPirateTargets(ShipInterface $ship): array
    {
        $layer = $ship->getLayer();
        if ($layer === null) {
            return [];
        }

        $range = $ship->getSensorRange() * 2;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s r
                WITH s.rumps_id = r.id
                JOIN %s u
                WITH s.user_id = u.id
                WHERE s.cx BETWEEN :minX AND :maxX
                AND s.cy BETWEEN :minY AND :maxY
                AND s.layer_id = :layerId
                AND s.type = :shipType
                AND r.prestige > 0
                AND u.id >= :firstUserId
                AND u.state >= :stateActive
                AND u.creation < :fourMonthEarlier
                AND (u.vac_active = false OR u.vac_request_date > :vacationThreshold)',
                Ship::class,
                ShipRump::class,
                User::class
            )
        )
            ->setParameters([
                'minX' => $ship->getCx() - $range,
                'maxX' => $ship->getCx() + $range,
                'minY' => $ship->getCY() - $range,
                'maxY' => $ship->getCY() + $range,
                'layerId' => $layer->getId(),
                'shipType' => SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP,
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'stateActive' => UserEnum::USER_STATE_ACTIVE,
                'fourMonthEarlier' => time() - TimeConstants::EIGHT_WEEKS_IN_SECONDS,
                'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS
            ])
            ->getResult();
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
