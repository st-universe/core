<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SplitWarpCoreOutput;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class SplitWarpCoreOutput implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SPLIT_WARP_CORE_OUTPUT';

    private ShipLoaderInterface $shipLoader;

    private ShipWrapperFactoryInterface $shipWrapperFactory;


    public function __construct(
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();


        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $value = request::postInt('value');
        if ($value < 0) {
            $value = 0;
        }
        if ($value > 100) {
            $value = 100;
        }
        $warpcoresystem = $this->shipWrapperFactory->wrapShip($ship)->getWarpCoreSystemData();

        $warpcoresystem->setWarpCoreSplit($value)->update();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
