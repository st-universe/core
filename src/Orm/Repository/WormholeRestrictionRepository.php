<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Ship\Wormhole\WormholeEntryTypeEnum;
use Stu\Orm\Entity\WormholeRestriction;
use Stu\Orm\Entity\WormholeEntry;

/**
 * @extends EntityRepository<WormholeRestriction>
 */
final class WormholeRestrictionRepository extends EntityRepository implements WormholeRestrictionRepositoryInterface
{
    #[\Override]
    public function prototype(): WormholeRestriction
    {
        return new WormholeRestriction();
    }

    #[\Override]
    public function save(WormholeRestriction $restriction): void
    {
        $em = $this->getEntityManager();
        $em->persist($restriction);
    }

    #[\Override]
    public function delete(WormholeRestriction $restriction): void
    {
        $em = $this->getEntityManager();

        $em->remove($restriction);
        $em->flush();
    }

    #[\Override]
    public function existsForTargetAndTypeAndEntry(int $targetId, ?WormholeEntryTypeEnum $privilegeType, WormholeEntry $wormholeEntry): bool
    {
        return $this->count([
            'wormholeEntry' => $wormholeEntry,
            'target' => $targetId,
            'privilege_type' => $privilegeType,
        ]) > 0;
    }

    #[\Override]
    public function truncateByTypeAndTarget(?WormholeEntryTypeEnum $type, int $targetId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s wr WHERE wr.target = :targetId AND wr.privilege_type = :typeId',
                    WormholeRestriction::class
                )
            )
            ->setParameters([
                'typeId' => $type,
                'targetId' => $targetId,
            ])
            ->execute();
    }
}
