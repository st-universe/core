<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\ColonySandboxInterface;

/**
 * @extends ObjectRepository<ColonySandbox>
 *
 * @method null|ColonySandboxInterface find(integer $id)
 * @method ColonySandboxInterface[] findAll()
 */
interface ColonySandboxRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonySandboxInterface;

    public function save(ColonySandboxInterface $colonySandbox): void;

    public function delete(ColonySandboxInterface $colonySandbox): void;
}
