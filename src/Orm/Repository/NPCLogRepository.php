<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\NPCLog;
use Stu\Orm\Entity\NPCLogInterface;


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
    public function prototype(): NPCLogInterface
    {
        return new NPCLog();
    }

    #[Override]
    public function save(NPCLogInterface $npclog): void
    {
        $em = $this->getEntityManager();

        $em->persist($npclog);
    }

    #[Override]
    public function delete(NPCLogInterface $npclog): void
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
