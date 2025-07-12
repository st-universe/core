<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Module\Ship\Lib\TFleetShipItem;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\PirateWrath;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserRegistration;

/**
 * @extends EntityRepository<Ship>
 */
final class ShipRepository extends EntityRepository implements ShipRepositoryInterface
{
    #[Override]
    public function save(Ship $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(Ship $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getByUserAndFleet(int $userId, ?int $fleetId): array
    {
        return $this->findBy(
            [
                'user_id' => $userId,
                'fleet_id' => $fleetId
            ],
            ['id' => 'asc']
        );
    }

    #[Override]
    public function getByLocationAndUser(Location $location, User $user): array
    {
        return $this->findBy([
            'user' => $user,
            'location' => $location,
        ], [
            'fleet_id' => 'desc',
            'is_fleet_leader' => 'desc',
            'id' => 'desc'
        ]);
    }

    #[Override]
    public function getPossibleFleetMembers(Ship $fleetLeader): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                JOIN %s sp
                WITH s.id = sp.id
                JOIN %s sc
                WITH sp = sc.spacecraft
                WHERE sp.location = :location
                AND s.fleet_id IS NULL
                AND sp.user = :user
                AND sc.state != :state
                ORDER BY sp.rump_id ASC, sp.name ASC',
                Ship::class,
                Spacecraft::class,
                SpacecraftCondition::class
            )
        )->setParameters([
            'location' => $fleetLeader->getLocation(),
            'state' => SpacecraftStateEnum::RETROFIT,
            'user' => $fleetLeader->getUser()
        ])->getResult();
    }

    #[Override]
    public function getWithTradeLicensePayment(
        int $userId,
        int $tradePostShipId,
        int $commodityId,
        int $amount
    ): array {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.user_id = :userId AND s.docked_to_id = :tradePostShipId AND s.id IN (
                    SELECT st.spacecraft_id FROM %s st WHERE st.commodity_id = :commodityId AND st.count >= :amount
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

    #[Override]
    public function getEscapePods(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                LEFT JOIN %s sr
                WITH s.rump_id = sr.id
                WHERE sr.category_id = :categoryId',
                Ship::class,
                SpacecraftRump::class
            )
        )->setParameters([
            'categoryId' => SpacecraftRumpCategoryEnum::SHIP_CATEGORY_ESCAPE_PODS->value
        ])->getResult();
    }

    #[Override]
    public function getEscapePodsByCrewOwner(User $user): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                LEFT JOIN %s sr
                WITH s.rump_id = sr.id
                LEFT JOIN %s sc
                WITH sc.spacecraft = s
                WHERE sr.category_id = :categoryId
                AND sc.user = :user',
                Ship::class,
                SpacecraftRump::class,
                CrewAssignment::class
            )
        )->setParameters([
            'categoryId' => SpacecraftRumpCategoryEnum::SHIP_CATEGORY_ESCAPE_PODS->value,
            'user' => $user
        ])->getResult();
    }

    #[Override]
    public function getFleetShipsScannerResults(
        Spacecraft $spacecraft,
        bool $showCloaked = false,
        Map|StarSystemMap|null $field = null
    ): array {

        $rsm = new ResultSetMapping();
        $rsm->addEntityResult(TFleetShipItem::class, 's');
        $rsm->addFieldResult('s', 'fleetname', 'fleet_name');
        $rsm->addFieldResult('s', 'isdefending', 'is_defending');
        $rsm->addFieldResult('s', 'isblocking', 'is_blocking');
        TFleetShipItem::addTSpacecraftItemFields($rsm);

        $location = $field ?? $spacecraft->getLocation();

        $query = $this->getEntityManager()->createNativeQuery(
            sprintf(
                'SELECT f.id as fleetid, f.name as fleetname, f.defended_colony_id is not null as isdefending,
                    f.blocked_colony_id is not null as isblocking, s.id as shipid, sp.rump_id as rumpid,
                    ss.mode as warpstate, twd.mode as tractorwarpstate, COALESCE(ss2.mode,0) as cloakstate, ss3.mode as shieldstate,
                    COALESCE(ss4.status,0) as uplinkstate, sp.type as spacecrafttype, sp.name as shipname,
                    sc.hull as hull, sp.max_huelle as maxhull, sc.shield as shield, sp.holding_web_id as webid, tw.finished_time as webfinishtime,
                    u.id as userid, u.username, r.category_id as rumpcategoryid, r.name as rumpname, r.role_id as rumproleid,
                    (SELECT count(*) > 0 FROM stu_ship_log sl WHERE sl.spacecraft_id = s.id AND sl.is_private = :false) as haslogbook,
                    (SELECT count(*) > 0 FROM stu_crew_assign ca WHERE ca.spacecraft_id = s.id) as hascrew
                FROM stu_ship s
                JOIN stu_spacecraft sp
                ON s.id = sp.id
                JOIN stu_spacecraft_condition sc
                ON sp.id = sc.spacecraft_id
                LEFT JOIN stu_spacecraft_system ss
                ON s.id = ss.spacecraft_id
                AND ss.system_type = :warpdriveType
                LEFT JOIN stu_spacecraft tractor
                ON tractor.tractored_ship_id = s.id
                LEFT JOIN stu_spacecraft_system twd
                ON tractor.id = twd.spacecraft_id
                AND twd.system_type = :warpdriveType
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
                ON sp.rump_id = r.id
                JOIN stu_fleets f
                ON s.fleet_id = f.id
                LEFT OUTER JOIN stu_tholian_web tw
                ON sp.holding_web_id = tw.id
                JOIN stu_user u
                ON sp.user_id = u.id
                WHERE sp.location_id = :locationId
                AND s.id != :ignoreId
                %s
                ORDER BY f.sort DESC, f.id DESC, (CASE WHEN s.is_fleet_leader = :true THEN 0 ELSE 1 END), r.category_id ASC, r.role_id ASC, r.id ASC, sp.name ASC',
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
            'true' => true
        ]);

        return $query->getResult();
    }

    #[Override]
    public function getAllDockedShips(): array
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.docked_to_id IS NOT NULL',
                Ship::class
            )
        )->getResult();
    }

    #[Override]
    public function getPirateTargets(SpacecraftWrapperInterface $wrapper): array
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
                JOIN %s l
                WITH s.location = l.id
                JOIN %s u
                WITH s.user = u
                JOIN %s ur
                WITH ur.user = u
                LEFT JOIN %s w
                WITH u = w.user
                WHERE l.layer_id = :layerId
                AND l.cx BETWEEN :minX AND :maxX
                AND l.cy BETWEEN :minY AND :maxY
                AND (s.fleet_id IS NULL OR s.is_fleet_leader = :true)
                AND u.id >= :firstUserId
                AND u.state >= :stateActive
                AND ur.creation < :eightWeeksEarlier
                AND (u.vac_active = :false OR u.vac_request_date > :vacationThreshold)
                AND COALESCE(w.protection_timeout, 0) < :currentTime',
                Ship::class,
                Location::class,
                User::class,
                UserRegistration::class,
                PirateWrath::class
            )
        )
            ->setParameters([
                'minX' => $location->getCx() - $range,
                'maxX' => $location->getCx() + $range,
                'minY' => $location->getCy() - $range,
                'maxY' => $location->getCy() + $range,
                'layerId' => $layer->getId(),
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'stateActive' => UserStateEnum::USER_STATE_ACTIVE->value,
                'eightWeeksEarlier' => time() - TimeConstants::EIGHT_WEEKS_IN_SECONDS,
                'vacationThreshold' => time() - UserEnum::VACATION_DELAY_IN_SECONDS,
                'currentTime' => time(),
                'true' => true,
                'false' => false
            ])
            ->getResult();
    }

    #[Override]
    public function getPirateFriends(SpacecraftWrapperInterface $wrapper): array
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
                JOIN %s l
                WITH s.location_id = l.id
                WHERE l.layer_id = :layerId
                AND l.cx BETWEEN :minX AND :maxX
                AND l.cy BETWEEN :minY AND :maxY
                AND s.id != :shipId
                AND s.user_id = :kazonUserId',
                Ship::class,
                Location::class
            )
        )
            ->setParameters([
                'minX' => $location->getCx() - $range,
                'maxX' => $location->getCx() + $range,
                'minY' => $location->getCy() - $range,
                'maxY' => $location->getCy() + $range,
                'layerId' => $layer->getId(),
                'shipId' => $wrapper->get()->getId(),
                'kazonUserId' => UserEnum::USER_NPC_KAZON
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
            'location_id' => 'asc',
            'fleet_id' => 'asc',
            'is_fleet_leader' => 'desc'
        ]);
    }
}
