<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\PrestigeLog;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<PrestigeLog>
 *
 * @method null|PrestigeLog find(integer $id)
 */
interface PrestigeLogRepositoryInterface extends ObjectRepository
{
    public function save(PrestigeLog $log): void;

    public function delete(PrestigeLog $log): void;

    public function prototype(): PrestigeLog;

    public function getSumByUser(User $user): int;

    /**
     * @return list<PrestigeLog>
     */
    public function getPrestigeHistory(User $user, int $maxResults): array;
}
