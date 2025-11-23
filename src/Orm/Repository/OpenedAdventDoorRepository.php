<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\OpenedAdventDoor;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<OpenedAdventDoor>
 */
final class OpenedAdventDoorRepository extends EntityRepository implements OpenedAdventDoorRepositoryInterface
{
    #[\Override]
    public function prototype(): OpenedAdventDoor
    {
        return new OpenedAdventDoor();
    }

    #[\Override]
    public function save(OpenedAdventDoor $openedadventdoor): void
    {
        $em = $this->getEntityManager();

        $em->persist($openedadventdoor);
    }

    #[\Override]
    public function getOpenedDoorsCountOfToday(User $user): int
    {
        return count($this->findBy([
            'user_id' => $user->getId(),
            'day' => (int)date("j"),
            'month' => (int)date("n"),
            'year' => (int)date("Y"),
        ]));
    }
}
