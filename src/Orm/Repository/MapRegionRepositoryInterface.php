<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\MapRegionInterface;

/**
 * @extends ObjectRepository<MapRegion>
 *
 * @method null|MapRegionInterface find(integer$id)
 * @method MapRegionInterface[] findAll()
 */
interface MapRegionRepositoryInterface extends ObjectRepository
{
}
