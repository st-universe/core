<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapRegion;

/**
 * @extends ObjectRepository<MapRegion>
 *
 * @method null|MapRegion find(integer$id)
 * @method MapRegion[] findAll()
 */
interface MapRegionRepositoryInterface extends ObjectRepository {}
