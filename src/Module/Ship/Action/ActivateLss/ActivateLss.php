<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateLss;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ActivateLss implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_LSS';

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_LSS, $game);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
