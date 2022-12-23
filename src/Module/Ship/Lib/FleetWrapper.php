<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\FleetInterface;

final class FleetWrapper implements FleetWrapperInterface
{
    private FleetInterface $fleet;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private GameControllerInterface $game;

    public function __construct(
        FleetInterface $fleet,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        GameControllerInterface $game
    ) {
        $this->fleet = $fleet;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->game = $game;
    }

    public function get(): FleetInterface
    {
        return $this->fleet;
    }

    public function getShipsWrappers(): array
    {
        return $this->shipWrapperFactory->wrapShips($this->get()->getShips()->toArray());
    }

    public function isForeignFleet(): bool
    {
        return $this->get()->getUser() !== $this->game->getUser();
    }
}
