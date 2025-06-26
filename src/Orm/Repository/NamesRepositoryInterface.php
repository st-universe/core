<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\Names;

/**
 * @extends ObjectRepository<Names>
 *
 * @method null|Names find(integer $id)
 * @method Names[] findAll()
 */
interface NamesRepositoryInterface extends ObjectRepository
{
    public function save(Names $name): void;

    /**
     * @return array<Names>
     */
    public function mostUnusedNames(): array;

    /**
     * @return array<Names>
     */
    public function getRandomFreeSystemNames(int $amount): array;
}
