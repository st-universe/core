<?php

declare(strict_types=1);

namespace Stu\Component\Ship\Refactor;

use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RefactorReactorRunner
{
    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function refactor(): void
    {
        foreach ($this->shipRepository->findAll() as $ship) {
            $wrapper = $this->shipWrapperFactory->wrapShip($ship);

            $reactorWrapper = $wrapper->getReactorWrapper();
            if ($reactorWrapper === null) {
                continue;
            }

            //$reactorWrapper
            //    ->setOutput($ship->getTheoreticalReactorOutput())
            //    ->setLoad($ship->getReactorLoad());
        }
    }
}
