<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\NPCLog;

/**
 * @extends EntityRepository<NPCLog>
 */
final class NPCLogRepository extends EntityRepository implements NPCLogRepositoryInterface
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
    public function getByFactionAndSearch(?int $factionId, int $limit, string $search, int $sourceUserId): array
    {
        $search = trim($search);

        $queryBuilder = $this->createQueryBuilder('nl')
            ->orderBy('nl.id', 'DESC')
            ->setMaxResults($limit);

        if ($factionId === null) {
            $queryBuilder->where('nl.faction_id IS NULL');
        } else {
            $queryBuilder
                ->where('nl.faction_id = :factionId')
                ->setParameter('factionId', $factionId);
        }

        if ($search !== '') {
            $queryBuilder
                ->andWhere('LOWER(nl.text) LIKE :search')
                ->setParameter('search', sprintf('%%%s%%', strtolower($search)));
        }

        if ($sourceUserId > 0) {
            $queryBuilder
                ->andWhere('nl.source_user_id = :sourceUserId')
                ->setParameter('sourceUserId', $sourceUserId);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function getAmountByFaction(?int $factionId): int
    {
        return $this->count([
            'faction_id' => $factionId
        ]);
    }

    #[\Override]
    public function prototype(): NPCLog
    {
        return new NPCLog();
    }

    #[\Override]
    public function save(NPCLog $npclog): void
    {
        $em = $this->getEntityManager();

        $em->persist($npclog);
    }

    #[\Override]
    public function delete(NPCLog $npclog): void
    {
        $em = $this->getEntityManager();

        $em->remove($npclog);
    }
}
