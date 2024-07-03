<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\FleetInterface;

final class FleetWrapper implements FleetWrapperInterface
{
    public function __construct(private FleetInterface $fleet, private ShipWrapperFactoryInterface $shipWrapperFactory, private GameControllerInterface $game, private bool $isSingleShips)
    {
    }

    #[Override]
    public function get(): FleetInterface
    {
        return $this->fleet;
    }

    #[Override]
    public function getLeadWrapper(): ShipWrapperInterface
    {
        return $this->shipWrapperFactory->wrapShip($this->fleet->getLeadShip());
    }

    #[Override]
    public function getShipWrappers(): Collection
    {
        return $this->shipWrapperFactory->wrapShips($this->fleet->getShips()->toArray());
    }

    #[Override]
    public function isForeignFleet(): bool
    {
        return !$this->isSingleShips && $this->fleet->getUser() !== $this->game->getUser();
    }
}
