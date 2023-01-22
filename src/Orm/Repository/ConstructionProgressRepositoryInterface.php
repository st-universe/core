<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\ConstructionProgressInterface;

/**
 * @extends ObjectRepository<ConstructionProgress>
 *
 * @method null|ConstructionProgressInterface find(integer $id)
 * @method ConstructionProgressInterface[] findAll()
 */
interface ConstructionProgressRepositoryInterface extends ObjectRepository
{
    public function getByShip(int $shipId): ?ConstructionProgressInterface;

    public function prototype(): ConstructionProgressInterface;

    public function save(ConstructionProgressInterface $constructionProgress): void;

    public function delete(ConstructionProgressInterface $constructionProgress): void;
}
