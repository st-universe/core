<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameConfig;
use Stu\Orm\Entity\GameConfigInterface;

/**
 * @extends ObjectRepository<GameConfig>
 *
 * @method GameConfigInterface[] findAll()
 */
interface GameConfigRepositoryInterface extends ObjectRepository
{
    public function save(GameConfigInterface $item): void;

    public function getByOption(int $optionId): ?GameConfigInterface;

    /**
     * Updates the game state by bypassing the EntityManager
     *
     * The game state has the requirement to be updated without interfering
     * the EntityManager. So use a more direct approach
     */
    public function updateGameState(int $state, Connection $connection): void;
}
