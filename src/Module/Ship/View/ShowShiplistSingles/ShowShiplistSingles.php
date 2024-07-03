<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShiplistSingles;

use Override;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowShiplistSingles implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPLIST_SINGLES';

    public function __construct(private ShipRepositoryInterface $shipRepository, private ShipWrapperFactoryInterface $shipWrapperFactory)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ships = $this->shipRepository->getByUserAndFleetAndType($userId, null, SpacecraftTypeEnum::SPACECRAFT_TYPE_SHIP);

        $game->setTemplateVar('SINGLESHIPWRAPPERS', $this->shipWrapperFactory->wrapShips($ships));
        $game->showMacro('html/shiplistSingles.twig');
    }
}
