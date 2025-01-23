<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistSingles;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowShiplistSingles implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPLIST_SINGLES';

    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ships = $this->shipRepository->getByUserAndFleet($userId, null);

        $game->setTemplateVar('SINGLESHIPWRAPPERS', $this->spacecraftWrapperFactory->wrapShips($ships));
        $game->showMacro('html/shiplistSingles.twig');
    }
}
