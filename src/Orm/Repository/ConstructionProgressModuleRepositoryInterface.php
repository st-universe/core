<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ConstructionProgressModule;

/**
 * @extends ObjectRepository<ConstructionProgressModule>
 *
 * @method null|ConstructionProgressModule find(integer $id)
 */
interface ConstructionProgressModuleRepositoryInterface extends ObjectRepository
{
    public function prototype(): ConstructionProgressModule;

    public function save(ConstructionProgressModule $constructionProgressModule): void;

    public function delete(ConstructionProgressModule $constructionProgressModule): void;

    public function truncateByProgress(int $progressId): void;
}
