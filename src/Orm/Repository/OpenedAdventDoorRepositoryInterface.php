<?php

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\OpenedAdventDoor;
use Stu\Orm\Entity\OpenedAdventDoorInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends ObjectRepository<OpenedAdventDoor>
 *
 * @method null|OpenedAdventDoorInterface find(integer $id)
 * @method OpenedAdventDoorInterface[] findAll()
 */
interface OpenedAdventDoorRepositoryInterface extends ObjectRepository
{
    public function prototype(): OpenedAdventDoorInterface;

    public function save(OpenedAdventDoorInterface $openedadventdoor): void;

    public function getOpenedDoorsCountOfToday(UserInterface $user): int;
}
