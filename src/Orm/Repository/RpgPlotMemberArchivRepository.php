<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RpgPlotMemberArchiv;

/**
 * @extends EntityRepository<RpgPlotMemberArchiv>
 */
final class RpgPlotMemberArchivRepository extends EntityRepository implements
    RpgPlotMemberArchivRepositoryInterface
{
    #[\Override]
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMemberArchiv
    {
        return $this->findOneBy([
            'plot_id' => $plotId,
            'user_id' => $userId
        ]);
    }

    #[\Override]
    public function prototype(): RpgPlotMemberArchiv
    {
        return new RpgPlotMemberArchiv();
    }

    #[\Override]
    public function save(RpgPlotMemberArchiv $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlotMember);
    }

    #[\Override]
    public function delete(RpgPlotMemberArchiv $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlotMember);
    }

    #[\Override]
    public function getByPlot(int $plotId): array
    {
        return $this->findBy([
            'plot_id' => $plotId
        ]);
    }

    #[\Override]
    public function getByPlotFormerId(int $plotFormerId): array
    {
        return $this->findBy([
            'plot_id' => $plotFormerId
        ]);
    }

    #[\Override]
    public function truncateAllEntities(): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s pm',
                    RpgPlotMemberArchiv::class
                )
            )
            ->execute();
    }
}
