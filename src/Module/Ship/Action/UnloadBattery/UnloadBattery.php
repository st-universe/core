<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UnloadBattery;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UnloadBattery implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_USE_BATTERY';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

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
        
        $load = request::postInt('ebattload');
        
        if ($load < 1) {
            return;
        }

        if (request::postString('fleet')) {
            $msg = [];
            $msg[] = _('Flottenbefehl ausgeführt: Ersatzbatterie entladen');

            foreach ($ship->getFleet()->getShips() as $ship) {
                $msg[] = $this->unloadBattery($ship, $load);
            }
            $game->addInformationMerge($msg);
            return;
        }
        
        $game->addInformation($this->unloadBattery($ship, $load));
    }

    private function unloadBattery(ShipInterface $ship, int $load): string
    {
        // cancel conditions
        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrewCount() === 0) {
            return sprintf(_('%s: Das Schiff hat keine Crew'), $ship->getName());
        }
        if (!$ship->getEBatt()) {
            return sprintf(_('%s: Die Ersatzbatterie ist leer'), $ship->getName());
        }
        if (!$ship->isEBattUseable()) {
            return sprintf(_('%s: Die Batterie kann erst wieder am ' . $ship->getEBattWaitingTime() . ' genutzt werden'), $ship->getName());
        }
        if ($ship->getEps() >= $ship->getMaxEps()) {
            return sprintf(_('%s: Der Energiespeicher ist voll'), $ship->getName());
        }

        // unload following
        if ($load > $ship->getEBatt()) {
            $load = $ship->getEBatt();
        }
        if ($load + $ship->getEps() > $ship->getMaxEps()) {
            $load = $ship->getMaxEps() - $ship->getEps();
        }
        $ship->setEBatt($ship->getEBatt() - $load);
        $ship->setEps($ship->getEps() + $load);
        $ship->setEBattWaitingTime(time() + $load * 60);

        $this->shipRepository->save($ship);

        return sprintf(_('%s: Die Ersatzbatterie wurde um %d Einheiten entladen'), $ship->getName(),
                            $load);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
