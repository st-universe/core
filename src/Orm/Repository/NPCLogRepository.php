<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\NPCLog;
use Stu\Orm\Entity\NPCLogInterface;


/**
 * @extends EntityRepository<NPCLog>
 */
final class NPCLogRepository extends EntityRepository implements NPCLogRepositoryInterface
{
    public function getRecent(): array
    {
        return $this->findBy(
            [],
            ['id' => 'desc'],
            10
        );
    }

    public function prototype(): NPCLogInterface
    {
        return new NPCLog();
    }

    public function save(NPCLogInterface $npclog): void
    {
        $em = $this->getEntityManager();

        $em->persist($npclog);
    }

    public function delete(NPCLogInterface $npclog): void
    {
        $em = $this->getEntityManager();

        $em->remove($npclog);
    }

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
