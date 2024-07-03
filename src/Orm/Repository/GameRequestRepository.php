<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameRequestInterface;

/**
 * @extends EntityRepository<GameRequest>
 *
 * @deprecated Use logfile logging
 */
final class GameRequestRepository extends EntityRepository implements GameRequestRepositoryInterface
{
    #[Override]
    public function prototype(): GameRequestInterface
    {
        return new GameRequest();
    }

    #[Override]
    public function save(GameRequestInterface $gameRequest): void
    {
        $em = $this->getEntityManager();
        $em->persist($gameRequest);
        $em->flush();
    }

    #[Override]
    public function delete(GameRequestInterface $gameRequest): void
    {
        $em = $this->getEntityManager();
        $em->remove($gameRequest);
    }

    #[Override]
    public function truncateAllGameRequests(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s gr',
                GameRequest::class
            )
        )->execute();
    }
}
