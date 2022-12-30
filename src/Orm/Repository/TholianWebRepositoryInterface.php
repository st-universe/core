<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TholianWebInterface;

/**
 * @method null|TholianWebInterface find(integer $id)
 */
interface TholianWebRepositoryInterface extends ObjectRepository
{
}
