<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SetYellowAlert;

use Override;
use request;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class SetYellowAlert implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_YELLOW_ALERT';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->helper->setAlertState(request::indInt('id'), ShipAlertStateEnum::ALERT_YELLOW, $game);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
