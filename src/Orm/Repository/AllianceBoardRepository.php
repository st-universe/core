<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\AllianceBoardInterface;

/**
 * @extends EntityRepository<AllianceBoard>
 */
final class AllianceBoardRepository extends EntityRepository implements AllianceBoardRepositoryInterface
{
    public function prototype(): AllianceBoardInterface
    {
        return new AllianceBoard();
    }

    public function save(AllianceBoardInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    public function delete(AllianceBoardInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    public function getByAlliance(int $allianceId): array
    {
        return $this->findBy([
            'alliance_id' => $allianceId
        ]);
    }
}
