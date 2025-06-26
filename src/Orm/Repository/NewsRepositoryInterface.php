<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\News;

/**
 * @extends ObjectRepository<News>
 */
interface NewsRepositoryInterface extends ObjectRepository
{
    /**
     * @return array<News>
     */
    public function getRecent(int $limit): array;
}
