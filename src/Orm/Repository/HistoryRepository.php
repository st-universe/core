<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\HistoryInterface;

/**
 * @extends EntityRepository<History>
 */
final class HistoryRepository extends EntityRepository implements HistoryRepositoryInterface
{
    #[Override]
    public function getRecent(): array
    {
        return $this->findBy(
            [],
            ['id' => 'desc'],
            10
        );
    }

    #[Override]
    public function getRecentWithoutPirate(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT h FROM %s h
                    WHERE COALESCE(h.source_user_id, 0) != :pirateId
                    AND COALESCE(h.target_user_id, 0) != :pirateId
                    ORDER BY h.id DESC',
                    History::class
                )
            )
            ->setParameter('pirateId', UserEnum::USER_NPC_KAZON)
            ->setMaxResults(10)
            ->getResult();
    }


    #[Override]
    public function getByTypeAndSearch(HistoryTypeEnum $type, int $limit): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT h FROM %s h
                    WHERE h.type = :typeId
                    ORDER BY h.id desc',
                    History::class
                )
            )->setParameters(
                [
                    'typeId' => $type->value
                ]
            )
            ->setMaxResults($limit)
            ->getResult();
    }

    #[Override]
    public function getByTypeAndSearchWithoutPirate(HistoryTypeEnum $type, int $limit): array
    {

        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT h FROM %s h
                    WHERE h.type = :typeId
                    AND COALESCE(h.source_user_id, 0) != :pirateId 
                    AND COALESCE(h.target_user_id, 0) != :pirateId
                    ORDER BY h.id desc',
                    History::class
                )
            )->setParameters(
                [
                    'typeId' => $type->value,
                    'pirateId' => UserEnum::USER_NPC_KAZON
                ]
            )
            ->setMaxResults($limit)
            ->getResult();
    }

    #[Override]
    public function getSumDestroyedByUser(int $source_user, int $target_user): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT COUNT(h.id) FROM %s h
                    WHERE h.type = 1
                    AND h.source_user_id = :source_user
                    AND h.target_user_id = :target_user',
                    History::class
                )
            )
            ->setParameters([
                'source_user' => $source_user,
                'target_user' => $target_user
            ])
            ->getSingleScalarResult();
    }


    #[Override]
    public function getAmountByType(int $typeId): int
    {
        return $this->count([
            'type' => $typeId
        ]);
    }

    #[Override]
    public function prototype(): HistoryInterface
    {
        return new History();
    }

    #[Override]
    public function save(HistoryInterface $history): void
    {
        $em = $this->getEntityManager();

        $em->persist($history);
    }

    #[Override]
    public function delete(HistoryInterface $history): void
    {
        $em = $this->getEntityManager();

        $em->remove($history);
    }

    #[Override]
    public function truncateAllEntities(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s h',
                History::class
            )
        )->execute();
    }
}
