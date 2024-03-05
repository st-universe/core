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
    /**
     * @return array<NamesInterface>
     */
    public function mostUnusedNames(): array;
}
