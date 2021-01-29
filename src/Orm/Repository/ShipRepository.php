<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

use Stu\Component\Ship\ShipEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipRump;
use Stu\Orm\Entity\ShipSystem;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\ShipStorage;
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
        $em->flush();
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

    public function getByInnerSystemLocation(
        int $starStstemId,
        int $sx,
        int $sy
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s
                WHERE s.systems_id = :starSystemId AND s.sx = :sx AND s.sy = :sy
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
            'starSystemId' => $starStstemId,
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
                WHERE s.systems_id is null AND s.cx = :cx AND s.cy = :cy
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
            ['id' => 'asc']
        );
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
            'categoryId' => ShipEnum::SHIP_CATEGORY_ESCAPE_PODS
        ])->getResult();
    }

    public function getDebrisFields(): iterable
    {
        return $this->findBy([
            'is_destroyed' => true,
        ]);
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

    public function getSensorResultInnerSystem(int $systemId, int $sx, int $sy, int $sensorRange): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('sysid', 'sysid', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('cloakcount', 'cloakcount', 'integer');
        $rsm->addScalarResult('type', 'type', 'integer');
        $rsm->addScalarResult('field_id', 'field_id', 'integer');
        return $this->getEntityManager()->createNativeQuery(
            'SELECT a.sx as posx,a.sy as posy,a.systems_id as sysid, count(distinct b.id) as shipcount, count(distinct c.id) as cloakcount, d.type, a.field_id
            FROM stu_sys_map a
            LEFT JOIN stu_ships b
                ON b.systems_id = a.systems_id AND b.sx = a.sx AND b.sy = a.sy
                AND NOT EXISTS (SELECT ss.id
                                    FROM stu_ships_systems ss
                                    WHERE b.id = ss.ships_id
                                    AND ss.system_type = :systemId
                                    AND ss.mode > 1)
            LEFT JOIN stu_ships c
                ON c.systems_id = a.systems_id AND c.sx = a.sx AND c.sy = a.sy
                AND EXISTS (SELECT ss2.id
                                    FROM stu_ships_systems ss2
                                    WHERE c.id = ss2.ships_id
                                    AND ss2.system_type = :systemId
                                    AND ss2.mode > 1)
            LEFT JOIN stu_map_ftypes d ON d.id = a.field_id WHERE
			a.systems_id = :starSystemId AND a.sx BETWEEN :sxStart AND :sxEnd AND a.sy BETWEEN :syStart AND :syEnd
            GROUP BY a.sy, a.sx, a.systems_id, d.type, a.field_id ORDER BY a.sy,a.sx',
            $rsm
        )->setParameters([
            'starSystemId' => $systemId,
            'sxStart' => $sx - $sensorRange,
            'sxEnd' => $sx + $sensorRange,
            'syStart' => $sy - $sensorRange,
            'syEnd' => $sy + $sensorRange,
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
        ])->getResult();
    }

    public function getSensorResultOuterSystem(int $cx, int $cy, int $sensorRange): iterable
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('posx', 'posx', 'integer');
        $rsm->addScalarResult('posy', 'posy', 'integer');
        $rsm->addScalarResult('shipcount', 'shipcount', 'integer');
        $rsm->addScalarResult('cloakcount', 'cloakcount', 'integer');
        $rsm->addScalarResult('type', 'type', 'integer');
        $rsm->addScalarResult('field_id', 'field_id', 'integer');
        return $this->getEntityManager()->createNativeQuery(
            'SELECT a.cx as posx,a.cy as posy, count(distinct b.id) as shipcount, count(distinct c.id) as cloakcount, d.type, a.field_id
            FROM stu_map a
            LEFT JOIN stu_ships b
                ON b.cx=a.cx AND b.cy=a.cy
                AND NOT EXISTS (SELECT ss.id
                                    FROM stu_ships_systems ss
                                    WHERE b.id = ss.ships_id
                                    AND ss.system_type = :systemId
                                    AND ss.mode > 1)
            LEFT JOIN stu_ships c
                ON c.cx = a.cx AND c.cy=a.cy
                AND EXISTS (SELECT ss2.id
                                    FROM stu_ships_systems ss2
                                    WHERE c.id = ss2.ships_id
                                    AND ss2.system_type = :systemId
                                    AND ss2.mode > 1)
            LEFT JOIN stu_map_ftypes d ON d.id = a.field_id
			WHERE a.cx BETWEEN :sxStart AND :sxEnd AND a.cy BETWEEN :syStart AND :syEnd GROUP BY a.cy, a.cx, d.type, a.field_id ORDER BY a.cy,a.cx',
            $rsm
        )->setParameters([
            'sxStart' => $cx - $sensorRange,
            'sxEnd' => $cx + $sensorRange,
            'syStart' => $cy - $sensorRange,
            'syEnd' => $cy + $sensorRange,
            'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
        ])->getResult();
    }


    public function getSingleShipScannerResults(
        ShipInterface $ship,
        bool $isBase,
        bool $showCloaked = false
    ): iterable {
        $cloakSql = sprintf(
            ' AND ( (s.user_id = %d) OR NOT EXISTS (SELECT ss.id
                            FROM %s ss
                            WHERE s.id = ss.ships_id
                            AND ss.system_type = %d
                            AND ss.mode > 1)) ',
            $ship->getUser()->getId(),
            ShipSystem::class,
            ShipSystemTypeEnum::SYSTEM_CLOAK
        );

        if ($ship->getSystem() === null) {
            $query = $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT s FROM %s s
                    WHERE s.systems_id is null
                    AND s.cx = :cx AND s.cy = :cy
                    AND s.fleets_id IS NULL
                    %s
                    AND s.is_base = :isBase AND s.id != :ignoreId',
                    Ship::class,
                    $showCloaked ? '' : $cloakSql
                )
            )->setParameters([
                'cx' => $ship->getCx(),
                'cy' => $ship->getCy(),
                'ignoreId' => $ship->getId(),
                'isBase' => $isBase
            ]);
        } else {
            $query = $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT s FROM %s s
                    WHERE s.systems_id = :starSystemId
                    AND s.sx = :sx AND s.sy = :sy AND s.fleets_id IS NULL
                    %s
                    AND s.is_base = :isBase AND s.id != :ignoreId',
                    Ship::class,
                    $showCloaked ? '' : $cloakSql
                )
            )->setParameters([
                'starSystemId' => $ship->getSystem()->getId(),
                'sx' => $ship->getSx(),
                'sy' => $ship->getSy(),
                'ignoreId' => $ship->getId(),
                'isBase' => $isBase
            ]);
        }
        return $query->getResult();
    }

    public function isCloakedShipAtLocation(
        ShipInterface $ship
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

        if ($ship->getSystem() === null) {
            $query = $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT COUNT(s.id) FROM %s s
                    WHERE s.systems_id is null
                    AND s.cx = :cx AND s.cy = :cy
                    AND s.fleets_id IS NULL
                    %s
                    AND s.user_id != :ignoreId',
                    Ship::class,
                    $cloakSql
                )
            )->setParameters([
                'cx' => $ship->getCx(),
                'cy' => $ship->getCy(),
                'ignoreId' => $ship->getUserId()
            ]);
        } else {
            $query = $this->getEntityManager()->createQuery(
                sprintf(
                    'SELECT COUNT(s.id) FROM %s s
                    WHERE s.systems_id = :starSystemId
                    AND s.sx = :sx AND s.sy = :sy
                    %s
                    AND s.user_id != :ignoreId',
                    Ship::class,
                    $cloakSql
                )
            )->setParameters([
                'starSystemId' => $ship->getSystem()->getId(),
                'sx' => $ship->getSx(),
                'sy' => $ship->getSy(),
                'ignoreId' => $ship->getUserId()
            ]);
        }
        return $query->getSingleScalarResult() > 0;
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
}
