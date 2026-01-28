<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;

/**
 * @extends EntityRepository<History>
 */
final class HistoryRepository extends EntityRepository implements HistoryRepositoryInterface
{
    #[\Override]
    public function getRecent(): array
    {
        return $this->findBy(
            [],
            ['id' => 'desc'],
            10
        );
    }

    #[\Override]
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
            ->setParameter('pirateId', UserConstants::USER_NPC_KAZON)
            ->setMaxResults(10)
            ->getResult();
    }


    #[\Override]
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

    #[\Override]
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
                    'pirateId' => UserConstants::USER_NPC_KAZON
                ]
            )
            ->setMaxResults($limit)
            ->getResult();
    }

    #[\Override]
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


    #[\Override]
    public function getAmountByType(int $typeId): int
    {
        return $this->count([
            'type' => $typeId
        ]);
    }

    #[\Override]
    public function getAmountIndexedByLocationId(Layer $layer, int $dateThreshold): iterable
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                'SELECT m, COUNT(distinct h.id) + COUNT(distinct sm_h.id) AS amount
                    FROM %s m
                    JOIN %s h WITH h.location = m
                    LEFT JOIN m.starSystem mss
                    LEFT JOIN %s ssm WITH ssm.starSystem = mss
                    LEFT JOIN %s sm_h WITH sm_h.location = ssm
                    WHERE m.layer = :layer
                    AND h.date >= :dateThreshold
                    AND (sm_h.date IS NULL OR sm_h.date >= :dateThreshold)
                    GROUP BY m',
                Map::class,
                History::class,
                StarSystemMap::class,
                History::class
                )
            )
            ->setParameters([
                'layer' => $layer,
                'dateThreshold' => $dateThreshold
        ])
            ->toIterable();
    }

    #[\Override]
    public function prototype(): History
    {
        return new History();
    }

    #[\Override]
    public function save(History $history): void
    {
        $em = $this->getEntityManager();

        $em->persist($history);
    }

    #[\Override]
    public function delete(History $history): void
    {
        $em = $this->getEntityManager();

        $em->remove($history);
    }
}
