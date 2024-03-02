<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\NamesInterface;

/**
 * @extends ObjectRepository<NamesInterface>
 *
 * @method null|NamesInterface find(integer $id)
 * @method NamesInterface[] findAll()
 */
interface NamesRepositoryInterface extends ObjectRepository
{
    public function prototype(): NamesInterface;

    public function save(NamesInterface $name): void;

    public function delete(NamesInterface $name): void;

    /**
     * @return array<NamesInterface>
     */
    public function mostUnusedNames(): array;
}
