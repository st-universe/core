<?php

namespace Stu\Orm\Repository;

use Doctrine\Common\Persistence\ObjectRepository;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @method null|ColonyInterface find(integer $id)
 * @method ColonyInterface[] findAll()
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

    public function getByPosition(?StarSystemInterface $starSystem, int $sx, int $sy): ?ColonyInterface;

    /**
     * @return ColonyInterface[]
     */
    public function getOrderedListByUser(UserInterface $user): iterable;

    /**
     * @return ColonyInterface[]
     */
    public function getByTick(int $tick): iterable;
}
