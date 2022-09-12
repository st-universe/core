<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrestigeLogInterface;

/**
 * @method null|PrestigeLogInterface find(integer $id)
 */
interface PrestigeLogRepositoryInterface extends ObjectRepository
{
    public function save(PrestigeLogInterface $log): void;

    public function delete(PrestigeLogInterface $log): void;

    public function prototype(): PrestigeLogInterface;
}
