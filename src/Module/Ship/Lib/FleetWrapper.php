<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\FleetInterface;

final class FleetWrapper implements FleetWrapperInterface
{
    private FleetInterface $fleet;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private GameControllerInterface $game;

    private bool $isSingleShips;

    public function __construct(
        FleetInterface $fleet,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        GameControllerInterface $game,
        bool $isSingleShips
    ) {
        $this->fleet = $fleet;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->game = $game;
        $this->isSingleShips = $isSingleShips;
    }

    public function get(): FleetInterface
    {
        return $this->fleet;
    }

    public function getLeadWrapper(): ShipWrapperInterface
    {
        return $this->shipWrapperFactory->wrapShip($this->get()->getLeadShip());
    }

    public function getShipWrappers(): Collection
    {
        return $this->shipWrapperFactory->wrapShips($this->get()->getShips()->toArray());
    }

    public function isForeignFleet(): bool
    {
        return !$this->isSingleShips && $this->get()->getUser() !== $this->game->getUser();
    }
}
