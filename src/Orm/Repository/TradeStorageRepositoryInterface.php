<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeStorageInterface;

/**
 * @method null|TradeStorageInterface find(integer $id)
 */
interface TradeStorageRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeStorageInterface;

    public function save(TradeStorageInterface $post): void;

    public function delete(TradeStorageInterface $post): void;
}
