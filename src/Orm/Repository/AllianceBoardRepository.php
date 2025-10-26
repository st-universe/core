<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\AllianceBoard;

/**
 * @extends EntityRepository<AllianceBoard>
 */
final class AllianceBoardRepository extends EntityRepository implements AllianceBoardRepositoryInterface
{
    #[\Override]
    public function prototype(): AllianceBoard
    {
        return new AllianceBoard();
    }

    #[\Override]
    public function save(AllianceBoard $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[\Override]
    public function delete(AllianceBoard $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[\Override]
    public function getByAlliance(int $allianceId): array
    {
        return $this->findBy([
            'alliance_id' => $allianceId
        ]);
    }
}
