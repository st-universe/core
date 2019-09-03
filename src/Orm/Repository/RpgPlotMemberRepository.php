<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\RpgPlotMemberInterface;

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
        $em->flush($rpgPlotMember);
    }

    public function delete(RpgPlotMemberInterface $rpgPlotMember): void
    {
        $em = $this->getEntityManager();

        $em->remove($rpgPlotMember);
        $em->flush($rpgPlotMember);
    }

    public function getByUser(int $userId): array
    {
        return $this->findBy([
            'user_id' => $userId
        ]);
    }

    public function getAmountByPlot(int $plotId): int
    {
        return $this->count([
            'plot_id' => $plotId
        ]);
    }
}