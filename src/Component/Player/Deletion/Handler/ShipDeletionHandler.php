<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ShipRemoverInterface $shipRemover;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRemoverInterface $shipRemover,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRemover = $shipRemover;
        $this->shipRepository = $shipRepository;
    }

    public function delete(UserInterface $user): void
    {
        foreach ($this->shipRepository->getByUser($user) as $obj) {
            $this->shipRemover->remove($obj);
        }
    }
}
