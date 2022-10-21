<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\WormholeEntryInterface;

/**
 * @method null|WormholeEntryInterface find(integer $id)
 */
interface WormholeEntryRepositoryInterface extends ObjectRepository
{
}
