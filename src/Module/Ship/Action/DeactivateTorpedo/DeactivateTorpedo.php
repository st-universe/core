<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateTorpedo;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Action\Deactivate\AbstractSystemDeactivator;

final class DeactivateTorpedo extends AbstractSystemDeactivator implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_TORPEDO';

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $this->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TORPEDO, _('Projektilwaffe'), $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
