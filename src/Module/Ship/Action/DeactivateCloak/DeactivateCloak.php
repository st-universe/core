<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateCloak;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class DeactivateCloak implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_CLOAK';

    private ActivatorDeactivatorHelperInterface $helper;

    private AlertRedHelperInterface $alertRedHelper;

    private ShipLoaderInterface $shipLoader;


    public function __construct(
        ActivatorDeactivatorHelperInterface $helper,
        ShipLoaderInterface $shipLoader,
        AlertRedHelperInterface $alertRedHelper
    ) {
        $this->helper = $helper;
        $this->alertRedHelper = $alertRedHelper;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_CLOAK, $game);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $informations = [];

        //Alarm-Rot check
        $shipsToShuffle = $this->alertRedHelper->checkForAlertRedShips($ship, $informations);
        shuffle($shipsToShuffle);
        foreach ($shipsToShuffle as $alertShip) {
            $this->alertRedHelper->performAttackCycle($alertShip, $ship, $informations);
        }
        $game->addInformationMergeDown($informations);

        if ($ship->getIsDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
