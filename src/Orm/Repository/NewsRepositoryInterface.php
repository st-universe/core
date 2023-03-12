<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\News;
use Stu\Orm\Entity\NewsInterface;

/**
 * @extends ObjectRepository<News>
 */
interface NewsRepositoryInterface extends ObjectRepository
{
    /**
     * @return list<NewsInterface>
     */
    public function getRecent(int $limit): array;
}
