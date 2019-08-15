<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\DatabaseEntryInterface;

interface DatabaseEntryRepositoryInterface extends ObjectRepository
{
    public function getByCategoryId(int $categoryId): array;

    public function prototype(): DatabaseEntryInterface;

    public function save(DatabaseEntryInterface $entry): void;

}
