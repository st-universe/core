<?php

namespace Stu\Orm\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ObjectRepository;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\LocationInterface;

/**
 * @extends ObjectRepository<Location>
 *
 * @method null|LocationInterface find(integer $id)
 */
interface LocationRepositoryInterface extends ObjectRepository
{
    /** @return array<CellDataInterface> */
    public function getAllianceShipcountLayerData(PanelBoundaries $boundaries, int $allianceId, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getUserShipcountLayerData(PanelBoundaries $boundaries, int $userId, ResultSetMapping $rsm): array;

    /** @return array<CellDataInterface> */
    public function getShipShipcountLayerData(PanelBoundaries $boundaries, int $shipId, ResultSetMapping $rsm): array;

    /**
     * @return array<LocationInterface>
     */
    public function getForSubspaceEllipseCreation(): array;

    /**
     * @return array<array{category_name: string, amount: int}>
     */
    public function getRumpCategoryInfo(LayerInterface $layer, int $cx, int $cy): array;
}
