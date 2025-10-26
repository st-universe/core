<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<RpgPlotMember>
 */
final class RpgPlotMemberRepository extends EntityRepository implements RpgPlotMemberRepositoryInterface
{
    #[\Override]
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMember
    {
        return $this->findOneBy([
            'plot_id' => $plotId,
            'user_id' => $userId
        ]);
    }

    #[\Override]
    public function prototype(): RpgPlotMember
    {
        return new RpgPlotMember();
    }

    #[\Override]
    public function save(RpgPlotMember $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlotMember);
    }

    #[\Override]
    public function delete(RpgPlotMember $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlotMember);
    }

    #[\Override]
    public function getByUser(User $user): array
    {
        return $this->findBy([
            'user' => $user
        ]);
    }

    #[\Override]
    public function getByPlot(int $plotId): array
    {
        return $this->findBy([
            'plot_id' => $plotId
        ]);
    }
}
