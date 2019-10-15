<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class BuildplanDeletionHandler implements PlayerDeletionHandlerInteface
{
    private $shipBuildplanRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }

    public function delete(UserInterface $user): void
    {
        $result = $this->shipBuildplanRepository->getByUser($user->getId());
        foreach ($result as $obj) {
            $this->shipBuildplanRepository->delete($obj);
        }
    }
}
