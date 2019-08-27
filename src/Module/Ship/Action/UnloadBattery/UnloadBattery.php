<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UnloadBattery;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use SystemActivationWrapper;

final class UnloadBattery implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_USE_BATTERY';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
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

        $wrapper = new SystemActivationWrapper($ship);
        if ($wrapper->getError()) {
            $game->addInformation($wrapper->getError());
            return;
        }
        if (!$ship->hasEmergencyBattery()) {
            return;
        }
        if (!$ship->getEBatt()) {
            $game->addInformation(_("Die Ersatzbatterie ist leer"));
            return;
        }
        if (!$ship->isEBattUseable()) {
            $game->addInformation("Die Batterie kann erst wieder am " . $ship->getEBattWaitingTime() . " genutzt werden");
            return;
        }
        if ($ship->getEps() >= $ship->getMaxEps()) {
            $game->addInformation("Der Energiespeicher ist voll");
            return;
        }
        $load = request::postInt('ebattload');
        // @todo Load errechnen
        if ($load < 1) {
            return;
        }
        if ($load > $ship->getEBatt()) {
            $load = $ship->getEBatt();
        }
        if ($load + $ship->getEps() > $ship->getMaxEps()) {
            $load = $ship->getMaxEps() - $ship->getEps();
        }
        $ship->lowerEBatt($load);
        $ship->upperEps($load);
        $ship->setEBattWaitingTime($load * 60);
        $ship->save();
        $game->addInformation("Die Batterie wurde um " . $load . " Einheiten entladen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
