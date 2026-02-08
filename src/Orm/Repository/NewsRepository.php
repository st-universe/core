<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\News;

/**
 * @extends EntityRepository<News>
 */
final class NewsRepository extends EntityRepository implements NewsRepositoryInterface
{
    #[\Override]
    public function save(News $news): void
    {
        $em = $this->getEntityManager();

        $em->persist($news);
    }

    #[\Override]
    public function delete(News $news): void
    {
        $em = $this->getEntityManager();
        $em->remove($news);
        $em->flush();
    }

    #[\Override]
    public function prototype(): News
    {
        return new News();
    }
    #[\Override]
    public function getRecent(int $limit): array
    {
        return $this->findBy(
            [],
            ['date' => 'desc'],
            $limit
        );
    }
}
