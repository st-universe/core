<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Override;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class BuildableRumpListItem implements BuildableRumpListItemInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private SpacecraftRump $shipRump,
        private User $currentUser
    ) {}

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
        return $this->spacecraftRepository->getAmountByUserAndRump(
            $this->currentUser->getId(),
            $this->shipRump->getId()
        );
    }

    #[Override]
    public function getBuildplanCount(): int
    {
        return $this->spacecraftBuildplanRepository->getCountByRumpAndUser(
            $this->shipRump->getId(),
            $this->currentUser->getId()
        );
    }
}
