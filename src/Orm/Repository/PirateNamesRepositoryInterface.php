<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PirateNamesInterface;

/**
 * @extends ObjectRepository<PirateNamesInterface>
 *
 * @method null|PirateNamesInterface find(integer $id)
 * @method PirateNamesInterface[] findAll()
 */
interface PirateNamesRepositoryInterface extends ObjectRepository
{
    public function prototype(): PirateNamesInterface;

    public function save(PirateNamesInterface $pirateName): void;

    public function delete(PirateNamesInterface $pirateName): void;

    /**
     * @return array<PirateNamesInterface>
     */
    public function mostUnusedNames(): array;
}
