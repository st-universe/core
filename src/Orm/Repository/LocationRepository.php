<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Location;

/**
 * @extends EntityRepository<Location>
 */
class LocationRepository extends EntityRepository implements LocationRepositoryInterface
{
    #[Override]
    public function getAllianceShipcountLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
             (SELECT count(distinct s.id)
                    FROM stu_ships s
                    JOIN stu_user u ON s.user_id = u.id
                    WHERE s.location_id = l.id
                    AND u.allys_id = :allyId) as shipcount
            FROM stu_location l
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'allyId' => $allianceId
        ])->getResult();
    }

    #[Override]
    public function getShipShipcountLayerData(PanelBoundaries $boundaries, int $shipId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct s.id)
                FROM stu_ships s
                WHERE s.location_id = l.id
                AND s.id = :shipId) as shipcount
            FROM stu_location l
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId
            GROUP BY l.cy, l.cx, l.id',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'shipId' => $shipId
        ])->getResult();
    }

    #[Override]
    public function getUserShipcountLayerData(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct s.id)
                FROM stu_ships s
                WHERE s.location_id = l.id
                AND s.user_id = :userId) as shipcount
            FROM stu_location l
            WHERE l.cx BETWEEN :xStart AND :xEnd
            AND l.cy BETWEEN :yStart AND :yEnd
            AND l.layer_id = :layerId
            GROUP BY l.cy, l.cx, l.id',
            $rsm
        )->setParameters([
            'xStart' => $boundaries->getMinX(),
            'xEnd' => $boundaries->getMaxX(),
            'yStart' => $boundaries->getMinY(),
            'yEnd' => $boundaries->getMaxY(),
            'layerId' => $boundaries->getParentId(),
            'userId' => $userId
        ])->getResult();
    }

    public function getForSubspaceEllipseCreation(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('location_id', 'location_id', 'integer');
        $rsm->addScalarResult('descriminator', 'descriminator', 'integer');

        $locationIds = $this->getEntityManager()
            ->createNativeQuery(
                'SELECT location_id, descriminator FROM (
                    SELECT coalesce(sum(r1.tractor_mass) / 10, 0)
                            + coalesce(sum(r2.tractor_mass), 0)
                            + coalesce((SELECT count(ca.id)
                                            FROM stu_crew_assign ca
                                            JOIN stu_ships s
                                            ON ca.ship_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND s.location_id = l.id
                                            AND NOT EXISTS (SELECT ss.id
                                                            FROM stu_ship_system ss
                                                            WHERE ss.ship_id = s.id
                                                            AND ss.system_type = :systemwarp
                                                            AND ss.mode > :mode))
                                        * (SELECT count(ss.id)
                                            FROM stu_ship_system ss
                                            JOIN stu_ships s
                                            ON ss.ship_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND NOT EXISTS (SELECT ss.id
                                                            FROM stu_ship_system ss
                                                            WHERE ss.ship_id = s.id
                                                            AND ss.system_type = :systemwarp
                                                            AND ss.mode > :mode)
                                            AND s.location_id = l.id
                                            AND ss.mode > :mode)
                                        * 100, 0) - :threshold as descriminator,
                        l.id AS location_id
                        FROM stu_location l
                        JOIN stu_ships s
                        ON s.location_id = l.id
                        LEFT JOIN stu_rumps r1
                        ON s.rumps_id = r1.id
                        and r1.category_id = :rumpCategory
                        LEFT JOIN stu_rumps r2
                        ON s.rumps_id = r2.id
                        AND r2.category_id != :rumpCategory
                        WHERE s.user_id >= :firstUserId
                        AND s.state != :state
                        AND NOT EXISTS (SELECT ss.id
                                        FROM stu_ship_system ss
                                        WHERE ss.ship_id = s.id
                                        AND ss.system_type = :systemwarp
                                        AND ss.mode > :mode)
                        GROUP BY l.id) AS foo
                    WHERE descriminator > 0',
                $rsm
            )
            ->setParameters([
                'threshold' => SubspaceEllipseHandler::MASS_CALCULATION_THRESHOLD,
                'rumpCategory' => ShipRumpEnum::SHIP_CATEGORY_STATION,
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'mode' => ShipSystemModeEnum::MODE_OFF,
                'state' => ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION,
                'systemwarp' => ShipSystemTypeEnum::SYSTEM_WARPDRIVE
            ])
            ->getResult();

        $finalIds = [];
        foreach ($locationIds as $entry) {
            $descriminator = $entry['descriminator'];

            if ((int)ceil($descriminator / 1_000_000 + 5) > random_int(1, 100)) {
                $finalIds[] = $entry['location_id'];
            }
        }

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT l FROM %s l
                    WHERE l.id in (:ids)',
                    Location::class
                )
            )
            ->setParameters([
                'ids' => $finalIds
            ])
            ->getResult();
    }


    #[Override]
    public function getRumpCategoryInfo(LayerInterface $layer, int $cx, int $cy): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('category_name', 'category_name', 'string');
        $rsm->addScalarResult('amount', 'amount', 'integer');

        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT rc.name as category_name, count(*) as amount
                FROM stu_ships s
                JOIN stu_rumps r
                ON s.rumps_id = r.id
                JOIN stu_rumps_categories rc
                ON r.category_id = rc.id
                JOIN stu_location l
                ON s.location_id = l.id
                WHERE l.layer_id = :layerId
                AND l.cx = :cx
                AND l.cy = :cy
                AND NOT EXISTS (SELECT ss.id
                                    FROM stu_ship_system ss
                                    WHERE s.id = ss.ship_id
                                    AND ss.system_type = :systemId
                                    AND ss.mode > 1)
                GROUP BY rc.name
                ORDER BY 2 desc',
                $rsm
            )
            ->setParameters([
                'layerId' => $layer->getId(),
                'cx' => $cx,
                'cy' => $cy,
                'systemId' => ShipSystemTypeEnum::SYSTEM_CLOAK
            ])
            ->getResult();
    }
}
