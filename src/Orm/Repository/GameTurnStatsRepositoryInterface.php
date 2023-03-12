<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\GameTurnStats;
use Stu\Orm\Entity\GameTurnStatsInterface;

/**
 * @extends ObjectRepository<GameTurnStats>
 *
 * @method GameTurnStatsInterface[] findAll()
 */
interface GameTurnStatsRepositoryInterface extends ObjectRepository
{
    public function prototype(): GameTurnStatsInterface;

    public function save(GameTurnStatsInterface $turn): void;

    public function delete(GameTurnStatsInterface $turn): void;

    public function getShipCount(): int;

    public function getShipCountManned(): int;

    public function getShipCountNpc(): int;

    public function getFlightSigs24h(): int;

    public function getFlightSigsSystem24h(): int;

    /**
     * @return array<GameTurnStatsInterface>
     */
    public function getLatestStats(int $amount, int $divisor): array;
}
