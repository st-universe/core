<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\History\HistoryTypeEnum;
use Stu\Orm\Entity\History;
use Stu\Orm\Entity\HistoryInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;

/**
 * @extends EntityRepository<History>
 */
final class HistoryRepository extends EntityRepository implements HistoryRepositoryInterface
{
    public function getRecent(): array
    {
        return $this->findBy(
            [],
            ['id' => 'desc'],
            10
        );
    }

    public function getRecentWithoutPirate(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT h FROM %s h
                WHERE h.source_user_id != :pirateId
                AND h.target_user_id != :pirateId
                ORDER BY h.id DESC',
                    History::class
                )
            )
            ->setParameter('pirateId', UserEnum::USER_NPC_KAZON)
            ->setMaxResults(10)
            ->getResult();
    }


    public function getByTypeAndSearch(HistoryTypeEnum $type, int $limit, $search): array
    {
        return $this->getEntityManager()
            ->createQuery(
                $search ? sprintf(
                    'SELECT h FROM %s h
                WHERE h.type = :typeId
                AND UPPER(h.text) like UPPER(:search)
                ORDER BY h.id desc',
                    History::class
                ) : sprintf(
                    'SELECT h FROM %s h
                WHERE h.type = :typeId
                ORDER BY h.id desc',
                    History::class
                )
            )->setParameters(
                $search ? [
                    'typeId' => $type->value,
                    'search' => sprintf('%%%s%%', $search)
                ] : ['typeId' => $type->value]
            )
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getByTypeAndSearchWithoutPirate(HistoryTypeEnum $type, int $limit, $search): array
    {
        return $this->getEntityManager()
            ->createQuery(
                $search ? sprintf(
                    'SELECT h FROM %s h
                WHERE h.type = :typeId
                AND UPPER(h.text) like UPPER(:search)
                AND h.source_user_id != :pirateId 
                AND h.target_user_id != :pirateId
                ORDER BY h.id desc',
                    History::class
                ) : sprintf(
                    'SELECT h FROM %s h
                WHERE h.type = :typeId
                AND h.source_user_id != :pirateId
                AND h.target_user_id != :pirateId
                ORDER BY h.id desc',
                    History::class
                )
            )->setParameters(
                $search ? [
                    'typeId' => $type->value,
                    'pirateId' => UserEnum::USER_NPC_KAZON,
                    'search' => sprintf('%%%s%%', $search)
                ] : ['typeId' => $type->value, 'pirateId' => UserEnum::USER_NPC_KAZON]
            )
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getAmountByType(int $typeId): int
    {
        return $this->count([
            'type' => $typeId
        ]);
    }

    public function prototype(): HistoryInterface
    {
        return new History();
    }

    public function save(HistoryInterface $history): void
    {
        $em = $this->getEntityManager();

        $em->persist($history);
    }

    public function delete(HistoryInterface $history): void
    {
        $em = $this->getEntityManager();

        $em->remove($history);
    }

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