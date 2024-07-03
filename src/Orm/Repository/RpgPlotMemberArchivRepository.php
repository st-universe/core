<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RpgPlotMemberArchiv;
use Stu\Orm\Entity\RpgPlotMemberArchivInterface;

/**
 * @extends EntityRepository<RpgPlotMemberArchiv>
 */
final class RpgPlotMemberArchivRepository extends EntityRepository implements RpgPlotMemberArchivRepositoryInterface
{
    #[Override]
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMemberArchivInterface
    {
        return $this->findOneBy([
            'plot_id' => $plotId,
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function prototype(): RpgPlotMemberArchivInterface
    {
        return new RpgPlotMemberArchiv();
    }

    #[Override]
    public function save(RpgPlotMemberArchivInterface $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlotMember);
    }

    #[Override]
    public function delete(RpgPlotMemberArchivInterface $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlotMember);
    }

    #[Override]
    public function getByPlot(int $plotId): array
    {
        return $this->findBy([
            'plot_id' => $plotId
        ]);
    }

    #[Override]
    public function truncateAllEntities(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s pm',
                RpgPlotMemberArchiv::class
            )
        )->execute();
    }
}
