<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\SpacecraftInterface;

/**
 * @extends ObjectRepository<Anomaly>
 *
 * @method null|AnomalyInterface find(integer $id)
 * @method AnomalyInterface[] findAll()
 */
interface AnomalyRepositoryInterface extends ObjectRepository
{
    public function prototype(): AnomalyInterface;

    public function save(AnomalyInterface $anomaly): void;

    public function delete(AnomalyInterface $anomaly): void;

    public function getByLocationAndType(LocationInterface $location, AnomalyTypeEnum $type): ?AnomalyInterface;

    /** @return array<AnomalyInterface> */
    public function findAllRoot(): array;

    public function getActiveCountByTypeWithoutParent(AnomalyTypeEnum $type): int;

    public function getClosestAnomalyDistance(SpacecraftInterface $spacecraft): ?int;
}
