<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BuildableRumpListItem implements BuildableRumpListItemInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private ShipBuildplanRepositoryInterface $shipBuildplanRepository, private ShipRumpInterface $shipRump, private UserInterface $currentUser)
    {
    }

    #[Override]
    public function getId(): int
    {
        return $this->shipRump->getId();
    }

    #[Override]
    public function getName(): string
    {
        return $this->shipRump->getName();
    }

    #[Override]
    public function getCategoryName(): string
    {
        return $this->shipRump->getShipRumpCategory()->getName();
    }

    #[Override]
    public function getActiveShipCount(): int
    {
        return $this->shipRepository->getAmountByUserAndRump(
            $this->currentUser->getId(),
            $this->shipRump->getId()
        );
    }

    #[Override]
    public function getBuildplanCount(): int
    {
        return $this->shipBuildplanRepository->getCountByRumpAndUser(
            $this->shipRump->getId(),
            $this->currentUser->getId()
        );
    }
}
