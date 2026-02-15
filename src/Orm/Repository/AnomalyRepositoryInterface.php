<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;

/**
 * @extends ObjectRepository<Anomaly>
 *
 * @method null|Anomaly find(integer $id)
 * @method Anomaly[] findAll()
 */
interface AnomalyRepositoryInterface extends ObjectRepository
{
    public function prototype(): Anomaly;

    public function save(Anomaly $anomaly): void;

    public function delete(Anomaly $anomaly): void;

    public function getByLocationAndType(Location $location, AnomalyTypeEnum $type): ?Anomaly;

    /** @return array<Anomaly> */
    public function findAllRoot(): array;

    public function getActiveCountByTypeWithoutParent(AnomalyTypeEnum $type): int;

    public function getClosestAnomalyDistance(SpacecraftWrapperInterface $wrapper): ?int;

    /**
     * Retrieves all locations with ionstorm anomalies.
     *
     * @return array<Location>
     */
    public function getLocationsWithIonstormAnomalies(Layer $layer): array;
}
