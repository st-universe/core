<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ShipRemoverInterface $shipRemover;

    private ShipRepositoryInterface $shipRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipRemoverInterface $shipRemover,
        ShipRepositoryInterface $shipRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->shipRemover = $shipRemover;
        $this->shipRepository = $shipRepository;
        $this->entityManager = $entityManager;
    }

    public function delete(UserInterface $user): void
    {
        foreach ($this->shipRepository->getByUser($user) as $obj) {
            $this->shipRemover->remove($obj);
            $this->entityManager->flush();
        }
    }
}
