<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\GameTurn;
use Stu\Orm\Entity\GameTurnInterface;

/**
 * @extends EntityRepository<GameTurn>
 */
final class GameTurnRepository extends EntityRepository implements GameTurnRepositoryInterface
{
    #[Override]
    public function getCurrent(): ?GameTurnInterface
    {
        return $this->findOneBy(
            [],
            ['turn' => 'desc']
        );
    }

    #[Override]
    public function prototype(): GameTurnInterface
    {
        return new GameTurn();
    }

    #[Override]
    public function save(GameTurnInterface $turn): void
    {
        $em = $this->getEntityManager();

        $em->persist($turn);
        $em->flush();
    }

    #[Override]
    public function delete(GameTurnInterface $turn): void
    {
        $em = $this->getEntityManager();

        $em->remove($turn);
    }

    #[Override]
    public function truncateAllGameTurns(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s gt',
                GameTurn::class
            )
        )->execute();
    }
}
