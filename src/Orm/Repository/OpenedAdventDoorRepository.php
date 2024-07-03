<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Override;
use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\OpenedAdventDoor;
use Stu\Orm\Entity\OpenedAdventDoorInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<OpenedAdventDoor>
 */
final class OpenedAdventDoorRepository extends EntityRepository implements OpenedAdventDoorRepositoryInterface
{
    #[Override]
    public function prototype(): OpenedAdventDoorInterface
    {
        return new OpenedAdventDoor();
    }

    #[Override]
    public function save(OpenedAdventDoorInterface $openedadventdoor): void
    {
        $em = $this->getEntityManager();

        $em->persist($openedadventdoor);
    }

    #[Override]
    public function getOpenedDoorsCountOfToday(UserInterface $user): int
    {
        return count($this->findBy([
            'user_id' => $user->getId(),
            'day' => (int)date("j"),
            'year' => (int)date("Y"),
        ]));
    }
}
