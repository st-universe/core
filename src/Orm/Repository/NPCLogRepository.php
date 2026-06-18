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
    public function getByFactionAndSearch(?int $factionId, int $limit, string $search, int $sourceUserId, bool $includeAdminView): array
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

        if (!$includeAdminView) {
            $queryBuilder
                ->andWhere('(nl.admin_view IS NULL OR nl.admin_view = :adminView)')
                ->setParameter('adminView', false);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    #[\Override]
    public function getAmountByFaction(?int $factionId, bool $includeAdminView): int
    {
        $queryBuilder = $this->createQueryBuilder('nl')
            ->select('COUNT(nl.id)');

        if ($factionId === null) {
            $queryBuilder->where('nl.faction_id IS NULL');
        } else {
            $queryBuilder
                ->where('nl.faction_id = :factionId')
                ->setParameter('factionId', $factionId);
        }

        if (!$includeAdminView) {
            $queryBuilder
                ->andWhere('(nl.admin_view IS NULL OR nl.admin_view = :adminView)')
                ->setParameter('adminView', false);
        }

        return (int) $queryBuilder
            ->getQuery()
            ->getSingleScalarResult();
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
