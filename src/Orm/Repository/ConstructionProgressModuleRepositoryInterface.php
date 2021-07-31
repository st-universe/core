<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\ConstructionProgressModuleInterface;

/**
 * @method null|ConstructionProgressModuleInterface find(integer $id)
 */
interface ConstructionProgressModuleRepositoryInterface extends ObjectRepository
{
    public function prototype(): ConstructionProgressModuleInterface;

    public function save(ConstructionProgressModuleInterface $constructionProgressModule): void;

    public function delete(ConstructionProgressModuleInterface $constructionProgressModule): void;

    public function truncateByProgress(int $progressId): void;
}
