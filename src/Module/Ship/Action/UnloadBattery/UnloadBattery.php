<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UnloadBattery;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UnloadBattery implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_USE_BATTERY';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() === 0) {
            $game->addInformation(_('Das Schiff hat keine Crew'));
            return;
        }

        if ($ship->getMaxEbatt() == 0) {
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
        $ship->setEBatt($ship->getEBatt() - $load);
        $ship->setEps($ship->getEps() + $load);
        $ship->setEBattWaitingTime($load * 60);

        $this->shipRepository->save($ship);

        $game->addInformation("Die Batterie wurde um " . $load . " Einheiten entladen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
