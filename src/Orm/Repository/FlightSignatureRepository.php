<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<FlightSignature>
 */
final class FlightSignatureRepository extends EntityRepository implements FlightSignatureRepositoryInterface
{
    #[\Override]
    public function prototype(): FlightSignature
    {
        return new FlightSignature();
    }

    #[\Override]
    public function saveAll(array $array): void
    {
        $em = $this->getEntityManager();

        foreach ($array as $obj) {
            $em->persist($obj);
        }
    }

    #[\Override]
    public function save(FlightSignature $item): void
    {
        $em = $this->getEntityManager();
        $em->persist($item);
    }

    #[\Override]
    public function getVisibleSignatureCount(Colony $colony): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(DISTINCT CONCAT(fs.ship_id, fs.ship_name)) as count
                    FROM %s fs
                    JOIN %s ssm
                    WITH fs.location = ssm
                    WHERE (fs.is_cloaked = :false AND fs.time > :maxAgeUncloaked
                      OR fs.is_cloaked = :true AND fs.time > :maxAgeCloaked)
                    AND ssm.sx = :sx
                    AND ssm.sy = :sy
                    AND ssm.systems_id = :systemsId
                    AND fs.user_id != :ignoreId',
                    FlightSignature::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'maxAgeUncloaked' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'maxAgeCloaked' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED,
                'sx' => $colony->getSx(),
                'sy' => $colony->getSy(),
                'systemsId' => $colony->getSystem()->getId(),
                'ignoreId' => $colony->getUserId(),
                'false' => false,
                'true' => true
            ])
            ->getSingleScalarResult();
    }




    #[\Override]
    public function getVisibleSignatures(int $locationId, int $ignoreId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT fs FROM %s fs
                    JOIN fs.location l
                    WHERE fs.time > :maxAge
                    AND l.id = :locationId
                    AND fs.user_id != :ignoreId
                    ORDER BY fs.time desc',
                    FlightSignature::class
                )
            )
            ->setParameters([
                'maxAge' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'locationId' => $locationId,
                'ignoreId' => $ignoreId
            ])
            ->getResult();
    }

    public function createSignatureRangeResultMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('minx', 'minx', 'integer');
        $rsm->addScalarResult('maxx', 'maxx', 'integer');
        $rsm->addScalarResult('miny', 'miny', 'integer');
        $rsm->addScalarResult('maxy', 'maxy', 'integer');

        return $rsm;
    }

    #[\Override]
    public function getSignatureRange(): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(l.cx),0) as minx, COALESCE(max(l.cx),0) as maxx,
                    COALESCE(min(l.cy),0) as miny, COALESCE(max(l.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_location l ON l.id = fs.location_id',
                $this->createSignatureRangeResultMapping()
            )
            ->getResult();
    }

    #[\Override]
    public function getSignatureRangeForShip(int $shipId): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(l.cx),0) as minx, COALESCE(max(l.cx),0) as maxx, COALESCE(min(l.cy),0) as miny, COALESCE(max(l.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_location l ON l.id = fs.location_id
                WHERE fs.ship_id = :shipId',
                $this->createSignatureRangeResultMapping()
            )
            ->setParameter('shipId', $shipId)
            ->getResult();
    }

    #[\Override]
    public function getSignatureRangeForUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(l.cx),0) as minx, COALESCE(max(l.cx),0) as maxx, COALESCE(min(l.cy),0) as miny, COALESCE(max(l.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_location l ON l.id = fs.location_id
                WHERE fs.user_id = :userId',
                $this->createSignatureRangeResultMapping()
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    #[\Override]
    public function getSignatureRangeForAlly(int $allyId): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(l.cx),0) as minx, COALESCE(max(l.cx),0) as maxx, COALESCE(min(l.cy),0) as miny, COALESCE(max(l.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_location l ON l.id = fs.location_id
                JOIN stu_user u ON fs.user_id = u.id
                WHERE u.allys_id = :allyId',
                $this->createSignatureRangeResultMapping()
            )
            ->setParameter('allyId', $allyId)
            ->getResult();
    }

    #[\Override]
    public function deleteOldSignatures(int $threshold): void
    {
        $q = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s fs WHERE fs.time < :maxAge',
                    FlightSignature::class
                )
            );
        $q->setParameter('maxAge', time() - $threshold);
        $q->execute();
    }

    #[\Override]
    public function getFlightsTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('sc', 'sc', 'integer');
        $rsm->addScalarResult('faction_id', 'factionid', 'integer');
        $rsm->addScalarResult('shipc', 'shipc', 'integer');

        return $this
            ->getEntityManager()
            ->createNativeQuery(
                'SELECT fs.user_id, count(*) as sc,
                (SELECT faction_id
                FROM stu_user u
                WHERE fs.user_id = u.id),
                count(distinct ship_id) as shipc
                FROM stu_flight_sig fs
                WHERE fs.to_direction != 0
                AND fs.user_id > :firstUserId
                AND fs.time > :maxAge
                GROUP BY fs.user_id
                ORDER BY 2 DESC
                LIMIT 10',
                $rsm
            )
            ->setParameters([
                'maxAge' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'firstUserId' => UserConstants::USER_FIRST_ID
            ])
            ->getResult();
    }

    #[\Override]
    public function getSignaturesForUser(User $user): int
    {
        return (int)$this
            ->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(fs.id)
                    FROM %s fs
                    WHERE fs.to_direction != 0
                    AND fs.user_id  = :userId
                    AND fs.time > :maxAge',
                    FlightSignature::class
                )
            )
            ->setParameters([
                'maxAge' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'userId' => $user->getId()
            ])
            ->getSingleScalarResult();
    }
    /**
     * @return array<array{FlightSignature, Spacecraft}>
     */
    #[\Override]
    public function getSignaturesInSensorRange(
        int $user_id,
        int $cx,
        int $cy,
        int $layer_id,
        int $sensorRange,
        int $timeThreshold
    ): array {

        $flightSignatures = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT fs
                        FROM %s fs
                        JOIN %s m WITH fs.location = m
                        JOIN %s ly WITH m.layer = ly
                        WHERE m.cx BETWEEN :minX AND :maxX
                        AND m.cy BETWEEN :minY AND :maxY
                        AND ly.id = :layerId
                        AND fs.time >= :minTime
                        AND fs.user_id != :userId
                        AND fs.is_cloaked = false
                        ORDER BY fs.ship_id ASC, fs.rump_id ASC, fs.time DESC',
                    FlightSignature::class,
                    Map::class,
                    Layer::class
                )
            )
            ->setParameters([
                'minX' => $cx - $sensorRange,
                'maxX' => $cx + $sensorRange,
                'minY' => $cy - $sensorRange,
                'maxY' => $cy + $sensorRange,
                'layerId' => $layer_id,
                'minTime' => $timeThreshold,
                'userId' => $user_id
            ])
            ->getResult();

        $latestPerShipAndRump = [];
        foreach ($flightSignatures as $flightSignature) {
            $shipId = $flightSignature->getShipId();
            $rumpId = $flightSignature->getRump()->getId();
            $key = $shipId . '_' . $rumpId;

            if (!isset($latestPerShipAndRump[$key])) {
                $spacecraft = $this->getEntityManager()
                    ->getRepository(Spacecraft::class)
                    ->find($shipId);

                if ($spacecraft) {
                    $latestPerShipAndRump[$key] = [$flightSignature, $spacecraft];
                }
            }
        }

        uasort($latestPerShipAndRump, function ($a, $b) {
            return $b[0]->getTime() - $a[0]->getTime();
        });

        return array_values($latestPerShipAndRump);
    }

    #[\Override]
    public function getAdminLiveMapFlightSignatures(int $layerId, int $minTime, int $limit): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('ship_id', 'ship_id', 'integer');
        $rsm->addScalarResult('ship_name', 'ship_name', 'string');
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('user_name', 'user_name', 'string');
        $rsm->addScalarResult('alliance_id', 'alliance_id', 'integer');
        $rsm->addScalarResult('alliance_name', 'alliance_name', 'string');
        $rsm->addScalarResult('rump_id', 'rump_id', 'integer');
        $rsm->addScalarResult('rump_name', 'rump_name', 'string');
        $rsm->addScalarResult('x', 'x', 'integer');
        $rsm->addScalarResult('y', 'y', 'integer');
        $rsm->addScalarResult('time', 'time', 'integer');
        $rsm->addScalarResult('from_direction', 'from_direction', 'integer');
        $rsm->addScalarResult('to_direction', 'to_direction', 'integer');
        $rsm->addScalarResult('in_system', 'in_system', 'boolean');
        $rsm->addScalarResult('system_name', 'system_name', 'string');
        $rsm->addScalarResult('is_cloaked', 'is_cloaked', 'boolean');

        return $this->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'SELECT fs.id,
                    fs.ship_id,
                    fs.ship_name,
                    fs.user_id,
                    u.username as user_name,
                    al.id as alliance_id,
                    al.name as alliance_name,
                    fs.rump_id,
                    r.name as rump_name,
                    CASE WHEN map_field.id IS NOT NULL THEN location.cx ELSE parent_location.cx END as x,
                    CASE WHEN map_field.id IS NOT NULL THEN location.cy ELSE parent_location.cy END as y,
                    fs.time,
                    fs.from_direction,
                    fs.to_direction,
                    CASE WHEN system_field.id IS NULL THEN false ELSE true END as in_system,
                    systems.name as system_name,
                    fs.is_cloaked
                FROM stu_flight_sig fs
                JOIN stu_location location
                ON fs.location_id = location.id
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
                ON u.id = fs.user_id
                LEFT JOIN stu_alliances al
                ON al.id = u.allys_id
                JOIN stu_rump r
                ON r.id = fs.rump_id
                WHERE fs.time >= :minTime
                AND COALESCE(location.layer_id, parent_location.layer_id) = :layerId
                AND (map_field.id IS NOT NULL OR parent_map.id IS NOT NULL)
                AND (fs.from_direction BETWEEN 1 AND 4 OR fs.to_direction BETWEEN 1 AND 4)
                ORDER BY fs.time DESC
                LIMIT %d',
                    $limit
                ),
                $rsm
            )
            ->setParameters([
                'layerId' => $layerId,
                'minTime' => $minTime
            ])
            ->getResult();
    }

    #[\Override]
    public function getAdminLiveMapFlightSignaturesForShip(int $layerId, int $minTime, int $shipId, int $limit): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');
        $rsm->addScalarResult('ship_id', 'ship_id', 'integer');
        $rsm->addScalarResult('ship_name', 'ship_name', 'string');
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('user_name', 'user_name', 'string');
        $rsm->addScalarResult('alliance_id', 'alliance_id', 'integer');
        $rsm->addScalarResult('alliance_name', 'alliance_name', 'string');
        $rsm->addScalarResult('rump_id', 'rump_id', 'integer');
        $rsm->addScalarResult('rump_name', 'rump_name', 'string');
        $rsm->addScalarResult('x', 'x', 'integer');
        $rsm->addScalarResult('y', 'y', 'integer');
        $rsm->addScalarResult('time', 'time', 'integer');
        $rsm->addScalarResult('from_direction', 'from_direction', 'integer');
        $rsm->addScalarResult('to_direction', 'to_direction', 'integer');
        $rsm->addScalarResult('in_system', 'in_system', 'boolean');
        $rsm->addScalarResult('system_name', 'system_name', 'string');
        $rsm->addScalarResult('is_cloaked', 'is_cloaked', 'boolean');

        return $this->getEntityManager()
            ->createNativeQuery(
                sprintf(
                    'SELECT fs.id,
                    fs.ship_id,
                    fs.ship_name,
                    fs.user_id,
                    u.username as user_name,
                    al.id as alliance_id,
                    al.name as alliance_name,
                    fs.rump_id,
                    r.name as rump_name,
                    CASE WHEN map_field.id IS NOT NULL THEN location.cx ELSE parent_location.cx END as x,
                    CASE WHEN map_field.id IS NOT NULL THEN location.cy ELSE parent_location.cy END as y,
                    fs.time,
                    fs.from_direction,
                    fs.to_direction,
                    CASE WHEN system_field.id IS NULL THEN false ELSE true END as in_system,
                    systems.name as system_name,
                    fs.is_cloaked
                FROM stu_flight_sig fs
                JOIN stu_location location
                ON fs.location_id = location.id
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
                ON u.id = fs.user_id
                LEFT JOIN stu_alliances al
                ON al.id = u.allys_id
                JOIN stu_rump r
                ON r.id = fs.rump_id
                WHERE fs.time >= :minTime
                AND fs.ship_id = :shipId
                AND COALESCE(location.layer_id, parent_location.layer_id) = :layerId
                AND (map_field.id IS NOT NULL OR parent_map.id IS NOT NULL)
                AND (fs.from_direction BETWEEN 1 AND 4 OR fs.to_direction BETWEEN 1 AND 4)
                ORDER BY fs.time DESC
                LIMIT %d',
                    $limit
                ),
                $rsm
            )
            ->setParameters([
                'layerId' => $layerId,
                'minTime' => $minTime,
                'shipId' => $shipId
            ])
            ->getResult();
    }

}
