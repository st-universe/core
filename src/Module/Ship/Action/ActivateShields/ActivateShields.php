<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateShields;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Tick\Ship\ShipTickInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ActivateShields implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ACTIVATE_SHIELDS';

    private ActivatorDeactivatorHelperInterface $helper;
    private ShipTickInterface $tick;
    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ActivatorDeactivatorHelperInterface $helper,
        ShipTickInterface $tick
    ) {
        $this->helper = $helper;
        $this->tick = $tick;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $this->tick->work($ship);

        $this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_SHIELDS, _('Schilde'), $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
