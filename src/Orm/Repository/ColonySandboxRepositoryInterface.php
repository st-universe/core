<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\ColonySandboxInterface;
use Stu\Orm\Entity\UserInterface;

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

    /** @return array<ColonySandboxInterface> */
    public function getByUser(UserInterface $user): array;

    public function truncateByColony(ColonyInterface $colony): void;
}
