<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

final class NewsRepository extends EntityRepository implements NewsRepositoryInterface
{
    public function getRecent(int $limit): array
    {
        return $this->findBy(
            [],
            ['date' => 'desc'],
            $limit
        );
    }
}