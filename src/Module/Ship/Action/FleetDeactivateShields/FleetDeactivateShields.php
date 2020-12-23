<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateShields;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\Deactivate\AbstractSystemDeactivator;

final class FleetDeactivateShields extends AbstractSystemDeactivator implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_SHIELDS';

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->deactivateFleet(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_SHIELDS, _('Schilde'), $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
