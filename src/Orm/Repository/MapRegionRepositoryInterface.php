<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\MapRegionInterface;

/**
 * @extends ObjectRepository<MapRegion>
 *
 * @method null|MapRegionInterface find(integer$id)
 */
interface MapRegionRepositoryInterface extends ObjectRepository
{
}
