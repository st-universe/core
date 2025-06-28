<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\KnPostArchiv;
use Stu\Orm\Entity\RpgPlotArchiv;

/**
 * @extends EntityRepository<KnPostArchiv>
 */
final class KnPostArchivRepository extends EntityRepository implements KnPostArchivRepositoryInterface
{
    #[Override]
    public function prototype(): KnPostArchiv
    {
        return new KnPostArchiv();
    }

    #[Override]
    public function save(KnPostArchiv $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
        $em->flush();
    }

    #[Override]
    public function delete(KnPostArchiv $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
    }

    #[Override]
    public function getBy(int $offset, int $limit): array
    {
        return $this->findBy(
            [],
            ['date' => 'desc'],
            $limit,
            $offset
        );
    }

    #[Override]
    public function getByUser(int $userId): array
    {
        return $this->findBy(
            ['user_id' => $userId],
            ['id' => 'desc']
        );
    }

    #[Override]
    public function getByPlot(RpgPlotArchiv $plot, ?int $offset, ?int $limit): array
    {
        return $this->findBy(
            ['plot_id' => $plot],
            ['date' => 'desc'],
            $limit,
            $offset
        );
    }

    #[Override]
    public function getByVersion(string $version, int $offset, int $limit): array
    {
        return $this->findBy(
            ['version' => $version],
            ['date' => 'desc'],
            $limit,
            $offset
        );
    }

    #[Override]
    public function getByVersionWithPlots(string $version, int $offset, int $limit): array
    {
        return $this->createQueryBuilder('kpa')
            ->where('kpa.version = :version')
            ->setParameter('version', $version)
            ->orderBy('kpa.date', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<int> $plotIds
     * @return array<int, RpgPlotArchiv>
     */
    #[Override]
    public function getPlotsByIds(array $plotIds): array
    {
        if (empty($plotIds)) {
            return [];
        }

        $plots = $this->getEntityManager()
            ->getRepository(RpgPlotArchiv::class)
            ->createQueryBuilder('rpa')
            ->where('rpa.former_id IN (:plotIds)')
            ->setParameter('plotIds', $plotIds)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($plots as $plot) {
            $result[$plot->getFormerId()] = $plot;
        }

        return $result;
    }

    #[Override]
    public function getAvailableVersions(): array
    {
        $result = $this->createQueryBuilder('kpa')
            ->select('DISTINCT kpa.version')
            ->orderBy('kpa.version', 'DESC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'version');
    }

    #[Override]
    public function getAmount(): int
    {
        return $this->count([]);
    }

    #[Override]
    public function getAmountByPlot(int $plotId): int
    {
        return $this->count([
            'plot_id' => $plotId
        ]);
    }

    #[Override]
    public function getAmountByVersion(string $version): int
    {
        return $this->count(['version' => $version]);
    }

    #[Override]
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

    #[Override]
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

    #[Override]
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

    #[Override]
    public function truncateAllEntities(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s kp',
                KnPostArchiv::class
            )
        )->execute();
    }

    #[Override]
    public function findByFormerId(int $formerId): ?KnPostArchiv
    {
        return $this->findOneBy(['former_id' => $formerId]);
    }

    #[Override]
    public function getByPlotFormerId(int $plotFormerId, ?int $offset, ?int $limit): array
    {
        return $this->findBy(
            ['plot_id' => $plotFormerId],
            ['date' => 'desc'],
            $limit,
            $offset
        );
    }
}
