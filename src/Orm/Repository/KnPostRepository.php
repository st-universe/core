<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;

/**
 * @extends EntityRepository<KnPost>
 */
final class KnPostRepository extends EntityRepository implements KnPostRepositoryInterface
{
    public function prototype(): KnPostInterface
    {
        return new KnPost();
    }

    public function save(KnPostInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    public function delete(KnPostInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getBy(int $offset, int $limit): array
    {
        return $this->findBy(
            [],
            ['date' => 'desc'],
            $limit,
            $offset
        );
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['id' => 'desc']
        );
    }

    public function getByPlot(RpgPlotInterface $plot, ?int $offset, ?int $limit): array
    {
        return $this->findBy(
            ['plot_id' => $plot],
            ['date' => 'desc'],
            $limit,
            $offset
        );
    }

    public function getAmount(): int
    {
        return $this->count([]);
    }

    public function getAmountByPlot(int $plotId): int
    {
        return $this->count([
            'plot_id' => $plotId
        ]);
    }

    public function getAmountSince(int $postId): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(p.id) FROM %s p WHERE p.id > :postId',
                    KnPost::class
                )
            )
            ->setParameters(['postId' => $postId])
            ->getSingleScalarResult();
    }

    public function getNewerThenMark(int $mark): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s p WHERE p.id > :postId',
                    KnPost::class
                )
            )
            ->setMaxResults(3)
            ->setParameters(['postId' => $mark])
            ->getResult();
    }

    public function searchByContent(string $content): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT p FROM %s p
                    WHERE UPPER(p.text) like UPPER(:content) OR UPPER(p.titel) like UPPER(:content)
                    ORDER BY p.id DESC',
                    KnPost::class
                )
            )
            ->setParameters(['content' => sprintf('%%%s%%', $content)])
            ->getResult();
    }
}
