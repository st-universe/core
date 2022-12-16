<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\UnloadBattery;

use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class UnloadBattery implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_USE_BATTERY';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipWrapperFactoryInterface $shipWrapperFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipWrapperFactory = $shipWrapperFactory;
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
        if (!$ship->hasEnoughCrew()) {
            return sprintf(_('%s: Das Schiff hat zu wenig Crew'), $ship->getName());
        }
        if (!$ship->getEBatt()) {
            return sprintf(_('%s: Die Ersatzbatterie ist leer'), $ship->getName());
        }
        if (!$ship->isEBattUseable()) {
            return sprintf(_('%s: Die Batterie kann erst wieder am ' . date('d.m.Y H:i', $ship->getEBattWaitingTime()) . ' genutzt werden'), $ship->getName());
        }
        if ($ship->getEps() >= $ship->getMaxEps()) {
            return sprintf(_('%s: Der Energiespeicher ist voll'), $ship->getName());
        }
        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_EPS)) {
            return sprintf(_('%s: Das Energienetz ist zerstört und kann nicht geladen werden'), $ship->getName());
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

        //experimental
        $eps = $this->shipWrapperFactory->wrapShip($ship)->getEpsShipSystem();
        $eps->setMaxBatt(42)->setBatt(55)->setBattWait(123)->update($ship, ShipSystemTypeEnum::SYSTEM_EPS);

        $this->shipRepository->save($ship);

        return sprintf(
            _('%s: Die Ersatzbatterie wurde um %d Einheiten entladen'),
            $ship->getName(),
            $load
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
