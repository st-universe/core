<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TorpedoHull;

/**
 * @extends ObjectRepository<TorpedoHull>
 *
 * @method null|TorpedoHull find(integer $id)
 */
interface TorpedoHullRepositoryInterface extends ObjectRepository
{
    public function prototype(): TorpedoHull;

    public function save(TorpedoHull $torpedohull): void;

    public function delete(TorpedoHull $torpedohull): void;

    /**
     * @return array<int>
     */
    public function getModificatorMinAndMax(): array;
}
