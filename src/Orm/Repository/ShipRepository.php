<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpSpecial;
use Stu\Orm\Entity\ShipStorage;

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
        $em->flush($post);
    }

    public function delete(ShipInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush($post);
    }

    public function getAmountByUserAndSpecialAbility(
        int $userId,
        int $specialAbilityId
    ): int {
        return (int)$this->getEntityManager()->createQuery(
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

    public function getByUser(int $userId): iterable
    {
        return $this->findBy([
            'user_id' => $userId,
        ]);
    }

    public function getByInnerSystemLocation(
        int $starStstemId,
        int $sx,
        int $sy
    ): iterable {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.systems_id = :starSystemId AND s.sx = :sx AND s.sy = :sy AND s.cloak = 0
                ORDER BY s.is_destroyed ASC, s.fleets_id DESC, s.id ASC',
                Ship::class
            )
        )->setParameters([
            'starSystemId' => $starStstemId,
            'sx' => $sx,
            'sy' => $sy,
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
            'amount' => $amount
        ])->getResult();
    }

    public function getSuitableForShildRegeneration(int $regenerationThreshold): iterable
    {
        return $this->getEntityManager()->createQuery(
            sprintf(
                'SELECT s FROM %s s WHERE s.is_destroyed = 0 AND s.schilde<s.max_schilde AND s.shield_regeneration_timer <= :regenerationThreshold',
                Ship::class
            )
        )->setParameters([
            'regenerationThreshold' => $regenerationThreshold
        ])->getResult();
    }

    public function getDebrisFields(): iterable
    {
        return $this->findBy([
            'is_destroyed' => true
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
            'SELECT sx as posx,sy as posy,systems_id as sysid,
			(SELECT count(id) FROM stu_ships WHERE sx=posx and sy=posy and cloak=0 AND systems_id=sysid) as shipcount,
			(SELECT count(id) FROM stu_ships where sx=posx AND sy=posy AND cloak=1 AND systems_id=sysid) as cloakcount,
			(SELECT type FROM stu_map_ftypes where id=field_id) as type,field_id FROM stu_sys_map WHERE
			systems_id = :starSystemId AND sx BETWEEN :sxStart AND :sxEnd AND sy BETWEEN :syStart AND :syEnd ORDER BY sy,sx',
            $rsm
        )->setParameters([
            'starSystemId' => $systemId,
            'sxStart' => $sx - $sensorRange,
            'sxEnd' => $sx + $sensorRange,
            'syStart' => $sy - $sensorRange,
            'syEnd' => $sy + $sensorRange,
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
            'SELECT cx as posx,cy as posy,
			(SELECT count(id) FROM stu_ships WHERE cx=posx and cy=posy and cloak=0) as shipcount,
			(SELECT count(id) FROM stu_ships where cx=posx AND cy=posy AND cloak=1) as cloakcount,
			(SELECT type FROM stu_map_ftypes where id=field_id) as type,field_id FROM stu_map WHERE
			cx BETWEEN :sxStart AND :sxEnd AND cy BETWEEN :syStart AND :syEnd ORDER BY cy,cx',
            $rsm
        )->setParameters([
            'sxStart' => $cx - $sensorRange,
            'sxEnd' => $cx + $sensorRange,
            'syStart' => $cy - $sensorRange,
            'syEnd' => $cy + $sensorRange,
        ])->getResult();
    }
}