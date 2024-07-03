<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\AllianceBoardInterface;

/**
 * @extends EntityRepository<AllianceBoard>
 */
final class AllianceBoardRepository extends EntityRepository implements AllianceBoardRepositoryInterface
{
    #[Override]
    public function prototype(): AllianceBoardInterface
    {
        return new AllianceBoard();
    }

    #[Override]
    public function save(AllianceBoardInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->persist($post);
    }

    #[Override]
    public function delete(AllianceBoardInterface $post): void
    {
        $em = $this->getEntityManager();

        $em->remove($post);
        $em->flush();
    }

    #[Override]
    public function getByAlliance(int $allianceId): array
    {
        return $this->findBy([
            'alliance_id' => $allianceId
        ]);
    }

    #[Override]
    public function truncateAllAllianceBoards(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ab',
                AllianceBoard::class
            )
        )->execute();
    }
}
