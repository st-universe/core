<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoHull;
use Stu\Orm\Entity\TorpedoHullInterface;

/**
 * @extends ObjectRepository<TorpedoHull>
 *
 * @method null|TorpedoHullInterface find(integer $id)
 */
interface TorpedoHullRepositoryInterface extends ObjectRepository
{
    public function prototype(): TorpedoHullInterface;

    public function save(TorpedoHullInterface $torpedohull): void;

    public function delete(TorpedoHullInterface $torpedohull): void;

    /**
     * @return array<int>
     */
    public function getModificatorMinAndMax(): array;
}
