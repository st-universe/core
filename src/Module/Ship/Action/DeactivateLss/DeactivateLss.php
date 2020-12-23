<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateLss;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\Deactivate\AbstractSystemDeactivator;

final class DeactivateLss extends AbstractSystemDeactivator implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_LSS';

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_LSS, _('Langstreckensensoren'), $game);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
