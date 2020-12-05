<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseEntryInterface;

/**
 * @method null|DatabaseEntryInterface find(integer $id)
 */
interface DatabaseEntryRepositoryInterface extends ObjectRepository
{
    public function getByCategoryId(int $categoryId): array;

    public function prototype(): DatabaseEntryInterface;

    public function save(DatabaseEntryInterface $entry): void;

}
