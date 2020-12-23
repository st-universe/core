<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivatePhaser;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\Deactivate\AbstractSystemDeactivator;

final class DeactivatePhaser extends AbstractSystemDeactivator implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_PHASER';

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_PHASER, _('Energiewaffe'), $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
