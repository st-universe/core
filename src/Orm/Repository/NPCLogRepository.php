<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\NPCLog;

/**
 * @extends EntityRepository<NPCLog>
 */
final class NPCLogRepository extends EntityRepository implements NPCLogRepositoryInterface
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
    public function prototype(): NPCLog
    {
        return new NPCLog();
    }

    #[Override]
    public function save(NPCLog $npclog): void
    {
        $em = $this->getEntityManager();

        $em->persist($npclog);
    }

    #[Override]
    public function delete(NPCLog $npclog): void
    {
        $em = $this->getEntityManager();

        $em->remove($npclog);
    }

    #[Override]
    public function truncateAllEntities(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s n',
                NPCLog::class
            )
        )->execute();
    }
}
