<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Fleet;

final class FleetWrapper implements FleetWrapperInterface
{
    public function __construct(
        private Fleet $fleet,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private GameControllerInterface $game,
        private bool $isSingleShips
    ) {}

    #[Override]
    public function get(): Fleet
    {
        return $this->fleet;
    }

    #[Override]
    public function getLeadWrapper(): ShipWrapperInterface
    {
        return $this->spacecraftWrapperFactory->wrapShip($this->fleet->getLeadShip());
    }

    #[Override]
    public function getShipWrappers(): Collection
    {
        return $this->spacecraftWrapperFactory->wrapShips($this->fleet->getShips()->toArray());
    }

    #[Override]
    public function isForeignFleet(): bool
    {
        return !$this->isSingleShips && $this->fleet->getUser() !== $this->game->getUser();
    }
}
