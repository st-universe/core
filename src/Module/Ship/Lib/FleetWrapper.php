<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\FleetInterface;

final class FleetWrapper implements FleetWrapperInterface
{
    private FleetInterface $fleet;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        FleetInterface $fleet,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->fleet = $fleet;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function get(): FleetInterface
    {
        return $this->fleet;
    }

    public function getShips(): array
    {
        return $this->shipWrapperFactory->wrapShips($this->get()->getShips()->toArray());
    }
}
