<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class BuildableRumpListItem implements BuildableRumpListItemInterface
{
    private ShipRepositoryInterface $shipRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipRumpInterface $shipRump;

    private UserInterface $currentUser;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipRumpInterface $shipRump,
        UserInterface $currentUser
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipRump = $shipRump;
        $this->currentUser = $currentUser;
    }

    public function getId(): int
    {
        return $this->shipRump->getId();
    }

    public function getName(): string
    {
        return $this->shipRump->getName();
    }

    public function getCategoryName(): string
    {
        return $this->shipRump->getShipRumpCategory()->getName();
    }

    public function getActiveShipCount(): int
    {
        return $this->shipRepository->getAmountByUserAndRump(
            $this->currentUser->getId(),
            $this->shipRump->getId()
        );
    }

    public function getBuildplanCount(): int
    {
        return $this->shipBuildplanRepository->getCountByRumpAndUser(
            $this->shipRump->getId(),
            $this->currentUser->getId()
        );
    }
}
