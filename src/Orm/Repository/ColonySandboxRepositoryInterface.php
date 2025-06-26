<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<ColonySandbox>
 *
 * @method null|ColonySandbox find(integer $id)
 * @method ColonySandbox[] findAll()
 */
interface ColonySandboxRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonySandbox;

    public function save(ColonySandbox $colonySandbox): void;

    public function delete(ColonySandbox $colonySandbox): void;

    /** @return array<ColonySandbox> */
    public function getByUser(User $user): array;

    public function truncateByColony(Colony $colony): void;
}
