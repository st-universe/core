<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ConstructionProgress;
use Stu\Orm\Entity\Station;

/**
 * @extends ObjectRepository<ConstructionProgress>
 *
 * @method null|ConstructionProgress find(integer $id)
 * @method ConstructionProgress[] findAll()
 */
interface ConstructionProgressRepositoryInterface extends ObjectRepository
{
    public function getByStation(Station $station): ?ConstructionProgress;

    public function prototype(): ConstructionProgress;

    public function save(ConstructionProgress $constructionProgress): void;

    public function delete(ConstructionProgress $constructionProgress): void;
}
