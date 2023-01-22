<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\RpgPlotMemberInterface;

/**
 * @extends EntityRepository<RpgPlotMember>
 */
final class RpgPlotMemberRepository extends EntityRepository implements RpgPlotMemberRepositoryInterface
{
    public function getByPlotAndUser(int $plotId, int $userId): ?RpgPlotMemberInterface
    {
        return $this->findOneBy([
            'plot_id' => $plotId,
            'user_id' => $userId
        ]);
    }

    public function prototype(): RpgPlotMemberInterface
    {
        return new RpgPlotMember();
    }

    public function save(RpgPlotMemberInterface $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->persist($rpgPlotMember);
    }

    public function delete(RpgPlotMemberInterface $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlotMember);
        $em->flush();
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    public function getByPlot(int $plotId): array
    {
        return $this->findBy([
            'plot_id' => $plotId
        ]);
    }
}
