<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseType;

/**
 * @extends ObjectRepository<DatabaseType>
 *
 * @method null|DatabaseType find(integer $id)
 */
interface DatabaseTypeRepositoryInterface extends ObjectRepository {}
