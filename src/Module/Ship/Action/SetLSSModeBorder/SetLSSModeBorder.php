<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SetLSSModeBorder;

use request;
use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class SetLSSModeNormal implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_LSS_BORDER';

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->helper->setLSSMode(request::indInt('id'), ShipLSSModeEnum::LSS_BORDER, $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
