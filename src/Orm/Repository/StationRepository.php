<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\TStationItem;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;

/**
 * @extends EntityRepository<Station>
 */
final class StationRepository extends EntityRepository implements StationRepositoryInterface
{
    #[Override]
    public function save(Station $station): void
    {
        $em = $this->getEntityManager();

        $em->persist($station);
    }

    #[Override]
    public function delete(Station $station): void
    {
        $em = $this->getEntityManager();

        $em->remove($station);
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            [
                'user_id' => $userId
            ],
            ['max_huelle' => 'desc', 'id' => 'asc']
        );
    }

    #[Override]
    public function getForeignStationsInBroadcastRange(Spacecraft $spacecraft): array
    {
        $layer = $spacecraft->getLayer();
        $systemMap = $spacecraft->getStarsystemMap();
        $map = $spacecraft->getMap();

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT st FROM %s st
                     JOIN %s s
                     WITH st.id = s.id
                     LEFT JOIN %s m
                     WITH s.location_id = m.id
                     LEFT JOIN %s l
                     WITH m.id = l.id
                     LEFT JOIN %s sm
                     WITH s.location_id = sm.id
                     WHERE s.user_id NOT IN (:ignoreIds)
                     AND (:layerId = 0 OR (l.layer_id = :layerId
                        AND l.cx BETWEEN (:cx - 1) AND (:cx + 1)
                        AND l.cy BETWEEN (:cy - 1) AND (:cy + 1)))
                     AND (:systemId = 0 OR (sm.systems_id = :systemId
                        AND sm.sx BETWEEN (:sx - 1) AND (:sx + 1)
                        AND sm.sy BETWEEN (:sy - 1) AND (:sy + 1)))',
                    Station::class,
                    Spacecraft::class,
                    Map::class,
                    Location::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'ignoreIds' => [$spacecraft->getUser()->getId(), UserEnum::USER_NOONE],
                'systemId' => $systemMap === null ? 0 : $systemMap->getSystem()->getId(),
                'sx' => $systemMap === null ? 0 : $systemMap->getSx(),
                'sy' => $systemMap === null ? 0 : $systemMap->getSy(),
                'layerId' => ($systemMap !== null || $layer === null) ? 0 : $layer->getId(),
                'cx' => ($systemMap !== null || $map === null) ? 0 : $map->getCx(),
                'cy' => ($systemMap !== null || $map === null) ? 0 : $map->getCy()
            ])
            ->getResult();
    }

    #[Override]
    public function getTradePostsWithoutDatabaseEntry(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s tp
                WITH s.tradePost = tp
                WHERE s.database_id is null',
                Station::class,
                TradePost::class
            )
        )->getResult();
    }

    #[Override]
    public function getByUplink(int $userId): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s sp
                WITH s.id = sp.id
                JOIN %s ca
                WITH s = ca.spacecraft
                JOIN %s c
                WITH ca.crew = c
                JOIN %s ss
                WITH ss.spacecraft_id = s.id
                JOIN %s u
                WITH sp.user_id = u.id
                WHERE sp.user_id != :userId
                AND c.user_id = :userId
                AND ss.system_type = :systemType
                AND ss.mode >= :mode
                AND (u.vac_active = :false OR u.vac_request_date > :vacationThreshold)',
                Station::class,
                Spacecraft::class,
                CrewAssignment::class,
                Crew::class,
                SpacecraftSystem::class,
                User::class
            )
        )->setParameters([
            'userId' => $userId,
            'systemType' => SpacecraftSystemTypeEnum::UPLINK->value,
            'mode' => SpacecraftSystemModeEnum::MODE_ON->value,
            'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS,
            'false' => false
        ])
            ->getResult();
    }

    #[Override]
    public function getStationConstructions(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s r
                WITH s.rump_id = r.id
                WHERE s.user_id > :firstUserId
                AND r.category_id = :catId',
                Spacecraft::class,
                SpacecraftRump::class
            )
        )->setParameters([
            'catId' => SpacecraftRumpCategoryEnum::SHIP_CATEGORY_CONSTRUCTION->value,
            'firstUserId' => UserEnum::USER_FIRST_ID
        ])
            ->getResult();
    }

    #[Override]
    public function getStationScannerResults(
        Spacecraft $spacecraft,
        bool $showCloaked = false,
        Map|StarSystemMap|null $field = null
    ): array {

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TStationItem::class, 's');
        TStationItem::addTSpacecraftItemFields($rsm);

        $location = $field ?? $spacecraft->getLocation();

        $query = $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT s.id as shipid, s.rump_id as rumpid , ss.mode as warpstate,
                    COALESCE(ss2.mode,0) as cloakstate, ss3.mode as shieldstate, COALESCE(ss4.status,0) as uplinkstate,
                    s.type as spacecrafttype, s.name as shipname, sc.hull as hull, s.max_huelle as maxhull,
                    sc.shield as shield, s.holding_web_id as webid, tw.finished_time as webfinishtime, u.id as userid, u.username,
                    r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_ship_log sl WHERE sl.spacecraft_id = s.id AND sl.is_private = :false) as haslogbook,
                    (SELECT count(*) > 0 FROM stu_crew_assign ca WHERE ca.spacecraft_id = s.id) as hascrew
                FROM stu_spacecraft s
                JOIN stu_spacecraft_condition sc
                ON s.id = sc.spacecraft_id
                JOIN stu_station st
                ON s.id = st.id
                LEFT JOIN stu_spacecraft_system ss
                ON s.id = ss.spacecraft_id
                AND ss.system_type = :warpdriveType
                LEFT JOIN stu_spacecraft_system ss2
                ON s.id = ss2.spacecraft_id
                AND ss2.system_type = :cloakType
                LEFT JOIN stu_spacecraft_system ss3
                ON s.id = ss3.spacecraft_id
                AND ss3.system_type = :shieldType
                LEFT JOIN stu_spacecraft_system ss4
                ON s.id = ss4.spacecraft_id
                AND ss4.system_type = :uplinkType
                JOIN stu_rump r
                ON s.rump_id = r.id
                LEFT OUTER JOIN stu_tholian_web tw
                ON s.holding_web_id = tw.id
                JOIN stu_user u
                ON s.user_id = u.id
                WHERE s.location_id = :locationId
                AND s.id != :ignoreId
                %s
                ORDER BY r.category_id ASC, r.role_id ASC, r.id ASC, s.name ASC',
                $showCloaked ? '' : sprintf(' AND (s.user_id = %d OR COALESCE(ss2.mode,0) < %d) ', $spacecraft->getUser()->getId(), SpacecraftSystemModeEnum::MODE_ON->value)
            ),
            $rsm
        )->setParameters([
            'locationId' => $location->getId(),
            'ignoreId' => $spacecraft->getId(),
            'cloakType' => SpacecraftSystemTypeEnum::CLOAK->value,
            'warpdriveType' => SpacecraftSystemTypeEnum::WARPDRIVE->value,
            'shieldType' => SpacecraftSystemTypeEnum::SHIELDS->value,
            'uplinkType' => SpacecraftSystemTypeEnum::UPLINK->value,
            'false' => false
        ]);

        return $query->getResult();
    }

    #[Override]
    public function getStationOnLocation(Location $location): ?Station
    {
        return $this->findOneBy(['location' => $location]);
    }

    #[Override]
    public function getStationsByUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT s
                    FROM %s s
                    JOIN %s r
                    WITH s.rump_id = r.id
                    WHERE s.user_id = :userId
                    AND r.category_id = :categoryId',
                    Station::class,
                    SpacecraftRump::class
                )
            )
            ->setParameters([
                'userId' => $userId,
                'categoryId' => SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION->value
            ])
            ->getResult();
    }

    #[Override]
    public function getPiratePhalanxTargets(SpacecraftWrapperInterface $wrapper): array
    {
        $layer = $wrapper->get()->getLayer();
        if ($layer === null) {
            return [];
        }

        $location = $wrapper->get()->getLocation();
        $range = $wrapper->getLssSystemData()?->getSensorRange() ?? 0;

        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s r WITH s.rump = r
                JOIN %s l WITH s.location = l
                JOIN %s u WITH s.user = u
                JOIN %s ur WITH ur.user = u
                LEFT JOIN %s w WITH u = w.user
                WHERE r.role_id = :phalanxRoleId
                AND l.layer_id = :layerId
                AND l.cx BETWEEN :minX AND :maxX
                AND l.cy BETWEEN :minY AND :maxY
                AND u.id >= :firstUserId
                AND u.state >= :stateActive
                AND ur.creation < :eightWeeksEarlier
                AND (u.vac_active = :false OR u.vac_request_date > :vacationThreshold)
                AND COALESCE(w.protection_timeout, 0) < :currentTime',
                Station::class,
                SpacecraftRump::class,
                Location::class,
                User::class,
                UserRegistration::class,
                PirateWrath::class
            )
        )
            ->setParameters([
                'phalanxRoleId' => SpacecraftRumpRoleEnum::SHIP_ROLE_SENSOR->value,
                'minX' => $location->getCx() - $range,
                'maxX' => $location->getCx() + $range,
                'minY' => $location->getCy() - $range,
                'maxY' => $location->getCy() + $range,
                'layerId' => $layer->getId(),
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'stateActive' => UserEnum::USER_STATE_ACTIVE,
                'eightWeeksEarlier' => time() - TimeConstants::EIGHT_WEEKS_IN_SECONDS,
                'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS,
                'currentTime' => time(),
                'false' => false
            ])
            ->getResult();
    }

    #[Override]
    public function getByUserAndRump(User $user, SpacecraftRump $rump): array
    {
        return $this->findBy([
            'user_id' => $user->getId(),
            'rump_id' => $rump->getId()
        ], [
            'location_id' => 'asc'
        ]);
    }
}
