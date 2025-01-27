<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Override;
use RuntimeException;
use Stu\Component\Anomaly\Type\SubspaceEllipseHandler;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\LocationInterface;

/**
 * @extends EntityRepository<Location>
 */
class LocationRepository extends EntityRepository implements LocationRepositoryInterface
{
    #[Override]
    public function getAllianceSpacecraftCountLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
             (SELECT count(distinct s.id)
                    FROM stu_spacecraft s
                    JOIN stu_user u ON s.user_id = u.id
                    WHERE s.location_id = l.id
                    AND u.allys_id = :allyId) as spacecraftcount
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
    public function getSpacecraftCountLayerDataForSpacecraft(PanelBoundaries $boundaries, int $spacecraftId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct s.id)
                FROM stu_spacecraft s
                WHERE s.location_id = l.id
                AND s.id = :spacecraftId) as spacecraftcount
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
            'spacecraftId' => $spacecraftId
        ])->getResult();
    }

    #[Override]
    public function getUserSpacecraftCountLayerData(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array
    {
        return $this->getEntityManager()->createNativeQuery(
            'SELECT l.cx as x, l.cy as y,
            (SELECT count(distinct s.id)
                FROM stu_spacecraft s
                WHERE s.location_id = l.id
                AND s.user_id = :userId) as spacecraftcount
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

    #[Override]
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
                                            JOIN stu_spacecraft s
                                            ON ca.spacecraft_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND s.location_id = l.id
                                            AND NOT EXISTS (SELECT ss.id
                                                            FROM stu_spacecraft_system ss
                                                            WHERE ss.spacecraft_id = s.id
                                                            AND ss.system_type = :systemwarp
                                                            AND ss.mode > :mode))
                                        * (SELECT count(ss.id)
                                            FROM stu_spacecraft_system ss
                                            JOIN stu_spacecraft s
                                            ON ss.spacecraft_id = s.id
                                            WHERE s.user_id >= :firstUserId
                                            AND s.state != :state
                                            AND NOT EXISTS (SELECT ss.id
                                                            FROM stu_spacecraft_system ss
                                                            WHERE ss.spacecraft_id = s.id
                                                            AND ss.system_type = :systemwarp
                                                            AND ss.mode > :mode)
                                            AND s.location_id = l.id
                                            AND ss.mode > :mode)
                                        * 100, 0) - :threshold as descriminator,
                        l.id AS location_id
                        FROM stu_location l
                        JOIN stu_spacecraft s
                        ON s.location_id = l.id
                        LEFT JOIN stu_rump r1
                        ON s.rump_id = r1.id
                        and r1.category_id = :rumpCategory
                        LEFT JOIN stu_rump r2
                        ON s.rump_id = r2.id
                        AND r2.category_id != :rumpCategory
                        WHERE s.user_id >= :firstUserId
                        AND s.state != :state
                        AND NOT EXISTS (SELECT ss.id
                                        FROM stu_spacecraft_system ss
                                        WHERE ss.spacecraft_id = s.id
                                        AND ss.system_type = :systemwarp
                                        AND ss.mode > :mode)
                        GROUP BY l.id) AS foo
                    WHERE descriminator > 0',
                $rsm
            )
            ->setParameters([
                'threshold' => SubspaceEllipseHandler::MASS_CALCULATION_THRESHOLD,
                'rumpCategory' => SpacecraftRumpEnum::SHIP_CATEGORY_STATION,
                'firstUserId' => UserEnum::USER_FIRST_ID,
                'mode' => SpacecraftSystemModeEnum::MODE_OFF->value,
                'state' => SpacecraftStateEnum::UNDER_CONSTRUCTION,
                'systemwarp' => SpacecraftSystemTypeEnum::WARPDRIVE
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
                FROM stu_spacecraft s
                JOIN stu_rump r
                ON s.rump_id = r.id
                JOIN stu_rumps_categories rc
                ON r.category_id = rc.id
                JOIN stu_location l
                ON s.location_id = l.id
                WHERE l.layer_id = :layerId
                AND l.cx = :cx
                AND l.cy = :cy
                AND NOT EXISTS (SELECT ss.id
                                    FROM stu_spacecraft_system ss
                                    WHERE s.id = ss.spacecraft_id
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
                'systemId' => SpacecraftSystemTypeEnum::CLOAK
            ])
            ->getResult();
    }

    #[Override]
    public function getRandomLocation(): LocationInterface
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id', 'integer');

        $randomId =  (int) $this->getEntityManager()
            ->createNativeQuery(
                'SELECT l.id, RANDOM() * (CASE WHEN l.discr = \'map\' THEN 1 ELSE 50 END)
                FROM stu_location l
                ORDER BY 2
                LIMIT 1',
                $rsm
            )
            ->getSingleScalarResult();

        $location = $this->find($randomId);
        if ($location === null) {
            throw new RuntimeException('this should not happen');
        }

        return $location;
    }
}
