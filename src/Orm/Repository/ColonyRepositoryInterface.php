<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;

/**
 * @method null|ColonyInterface find(integer $id)
 */
interface ColonyRepositoryInterface extends ObjectRepository
{
    public function prototype(): ColonyInterface;

    public function save(ColonyInterface $post): void;

    public function delete(ColonyInterface $post): void;

    public function getAmountByUser(int $userId, bool $isMoon = false): int;

    /**
     * @return ColonyInterface[]
     */
    public function getStartingByFaction(int $factionId): iterable;

    public function getByPosition(int $systemId, int $sx, int $sy): ?ColonyInterface;

    /**
     * @return ColonyInterface[]
     */
    public function getOrderedListByUser(int $userId): iterable;

    /**
     * @return ColonyInterface[]
     */
    public function getByTick(int $tick): iterable;
}