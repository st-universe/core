<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\KnPostArchivInterface;
use Stu\Orm\Entity\RpgPlotArchivInterface;

/**
 * @extends EntityRepository<KnPostArchiv>
 */
final class KnPostArchivRepository extends EntityRepository implements KnPostArchivRepositoryInterface
{
    public function prototype(): KnPostArchivInterface
    {
        return new KnPostArchiv();
    }

    public function save(KnPostArchivInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    public function delete(KnPostArchivInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
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

    public function getByPlot(RpgPlotArchivInterface $plot, ?int $offset, ?int $limit): array
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
                    KnPostArchiv::class
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
                    'SELECT p FROM %s p WHERE p.id > :postId ORDER BY p.id ASC',
                    KnPostArchiv::class
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
                    KnPostArchiv::class
                )
            )
            ->setParameters(['content' => sprintf('%%%s%%', $content)])
            ->getResult();
    }

    public function truncateAllEntities(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s kp',
                KnPostArchiv::class
            )
        )->execute();
    }
}
