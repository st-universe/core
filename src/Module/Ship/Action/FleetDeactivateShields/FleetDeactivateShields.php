<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateShields;

use Override;
use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class FleetDeactivateShields implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_SHIELDS';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->helper->deactivateFleet(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_SHIELDS, $game);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
