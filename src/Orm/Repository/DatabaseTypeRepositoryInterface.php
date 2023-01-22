<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseType;
use Stu\Orm\Entity\DatabaseTypeInterface;

/**
 * @extends ObjectRepository<DatabaseType>
 *
 * @method null|DatabaseTypeInterface find(integer $id)
 */
interface DatabaseTypeRepositoryInterface extends ObjectRepository
{

}
