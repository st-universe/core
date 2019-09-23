<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\MapRegionInterface;

/**
 * @method null|MapRegionInterface find(integer$id)
 */
interface MapRegionRepositoryInterface extends ObjectRepository
{
}