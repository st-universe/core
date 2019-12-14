<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\NewsInterface;

interface NewsRepositoryInterface extends ObjectRepository
{
    /**
     * @return NewsInterface[]
     */
    public function getRecent(int $limit): array;
}