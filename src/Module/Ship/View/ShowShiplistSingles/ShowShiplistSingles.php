<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistSingles;

use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowShiplistSingles implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPLIST_SINGLES';

    private ShipRepositoryInterface $shipRepository;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipWrapperFactory = $shipWrapperFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ships = $this->shipRepository->getByUserAndFleetAndType($userId, null, SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP);

        $game->setTemplateVar('SINGLESHIPWRAPPERS', $this->shipWrapperFactory->wrapShips($ships));
        $game->showMacro('html/shiplistSingles.twig');
    }
}
