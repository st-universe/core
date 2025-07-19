<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\News;

/**
 * @extends ObjectRepository<News>
 */
interface NewsRepositoryInterface extends ObjectRepository
{

    public function save(News $news): void;

    public function delete(News $news): void;

    public function prototype(): News;
    /**
     * @return array<News>
     */
    public function getRecent(int $limit): array;
}
