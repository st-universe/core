<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\News;

/**
 * @extends EntityRepository<News>
 */
final class NewsRepository extends EntityRepository implements NewsRepositoryInterface
{
    #[Override]
    public function getRecent(int $limit): array
    {
        return $this->findBy(
            [],
            ['date' => 'desc'],
            $limit
        );
    }
}
