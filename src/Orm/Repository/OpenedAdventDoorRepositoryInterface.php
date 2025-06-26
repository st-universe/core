<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\OpenedAdventDoor;
use Stu\Orm\Entity\User;

/**
 * @extends ObjectRepository<OpenedAdventDoor>
 *
 * @method null|OpenedAdventDoor find(integer $id)
 * @method OpenedAdventDoor[] findAll()
 */
interface OpenedAdventDoorRepositoryInterface extends ObjectRepository
{
    public function prototype(): OpenedAdventDoor;

    public function save(OpenedAdventDoor $openedadventdoor): void;

    public function getOpenedDoorsCountOfToday(User $user): int;
}
