<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\GameTurn;

/**
 * @extends EntityRepository<GameTurn>
 */
final class GameTurnRepository extends EntityRepository implements GameTurnRepositoryInterface
{
    #[\Override]
    public function getCurrent(): ?GameTurn
    {
        return $this->findOneBy(
            [],
            ['turn' => 'desc']
        );
    }

    #[\Override]
    public function prototype(): GameTurn
    {
        return new GameTurn();
    }

    #[\Override]
    public function save(GameTurn $turn): void
    {
        $em = $this->getEntityManager();

        $em->persist($turn);
        $em->flush();
    }

    #[\Override]
    public function delete(GameTurn $turn): void
    {
        $em = $this->getEntityManager();

        $em->remove($turn);
    }
}
