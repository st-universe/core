<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\UserInterface;

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
                    'SELECT p FROM %s p WHERE p.id > :postId ORDER BY p.id ASC',
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

    public function truncateAllEntities(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s kp',
                KnPost::class
            )
        )->execute();
    }

    public function getRpgVotesTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('votes', 'votes', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT kn.user_id, SUM(value::float) AS votes
            FROM stu_kn kn
            CROSS JOIN LATERAL json_each_text(kn.ratings)
            WHERE kn.user_id >= :firstUserId
            GROUP BY kn.user_id
            ORDER BY votes DESC
            LIMIT 10',
            $rsm
        )
            ->setParameter('firstUserId', UserEnum::USER_FIRST_ID)
            ->getResult();
    }

    public function getRpgVotesOfUser(UserInterface $user): ?int
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('votes', 'votes', 'integer');

        $result = $this->getEntityManager()->createNativeQuery(
            'SELECT SUM(value::int) as votes
            FROM stu_kn kn
            CROSS JOIN LATERAL json_each_text(kn.ratings)
            WHERE kn.user_id = :userId',
            $rsm
        )
            ->setParameter('userId', $user->getId())
            ->getSingleScalarResult();

        if ($result === null) {
            return null;
        }

        return (int) $result;
    }
}
