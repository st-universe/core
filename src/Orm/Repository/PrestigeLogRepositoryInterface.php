<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\PrestigeLogInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<PrestigeLog>
 *
 * @method null|PrestigeLogInterface find(integer $id)
 */
interface PrestigeLogRepositoryInterface extends ObjectRepository
{
    public function save(PrestigeLogInterface $log): void;

    public function delete(PrestigeLogInterface $log): void;

    public function prototype(): PrestigeLogInterface;

    public function getSumByUser(UserInterface $user): int;

    /**
     * @return array<PrestigeLogInterface>
     */
    public function getPrestigeHistory(UserInterface $user, int $maxResults): array;
}
