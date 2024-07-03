<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<RpgPlotMember>
 */
final class RpgPlotMemberRepository extends EntityRepository implements RpgPlotMemberRepositoryInterface
{
    #[Override]
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMemberInterface
    {
        return $this->findOneBy([
            'plot_id' => $plotId,
            'user_id' => $userId
        ]);
    }

    #[Override]
    public function prototype(): RpgPlotMemberInterface
    {
        return new RpgPlotMember();
    }

    #[Override]
    public function save(RpgPlotMemberInterface $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlotMember);
    }

    #[Override]
    public function delete(RpgPlotMemberInterface $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlotMember);
    }

    #[Override]
    public function getByUser(UserInterface $user): array
    {
        return $this->findBy([
            'user' => $user
        ]);
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
                RpgPlotMember::class
            )
        )->execute();
    }
}
