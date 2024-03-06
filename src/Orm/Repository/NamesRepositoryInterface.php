<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Names;
use Stu\Orm\Entity\NamesInterface;

/**
 * @extends ObjectRepository<Names>
 *
 * @method null|NamesInterface find(integer $id)
 * @method NamesInterface[] findAll()
 */
interface NamesRepositoryInterface extends ObjectRepository
{
    public function save(NamesInterface $name): void;

    /**
     * @return array<NamesInterface>
     */
    public function mostUnusedNames(): array;

    /**
     * @return array<NamesInterface>
     */
    public function getRandomFreeSystemNames(int $amount): array;
}
