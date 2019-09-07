<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseTypeInterface;

/**
 * @method null|DatabaseTypeInterface find(integer $id)
 */
interface DatabaseTypeRepositoryInterface extends ObjectRepository
{

}
