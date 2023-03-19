<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LoadReactor;

use request;
use Stu\Component\Ship\ShipEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ReactorUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LoadReactor implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LOAD_REACTOR';

    private ShipLoaderInterface $shipLoader;

    private ReactorUtilInterface $reactorUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ReactorUtilInterface $reactorUtil,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->reactorUtil = $reactorUtil;
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

        $requestedLoad = (int) request::postIntFatal('reactorload');

        if (request::postString('fleet')) {
            $msg = [];
            $msg[] = _('Flottenbefehl ausgeführt: Aufladung des Reaktors');
            foreach ($ship->getFleet()->getShips() as $ship) {
                $hasWarpcore = $ship->hasWarpcore();
                $hasFusionReactor = $ship->hasFusionReactor();

                if (!$hasWarpcore && !$hasFusionReactor) {
                    $game->addInformation(sprintf(
                        _('%s: Kein Reaktor vorhanden'),
                        $ship->getName()
                    ));
                    continue;
                }

                if (!$ship->hasEnoughCrew()) {
                    $game->addInformation(sprintf(
                        _('%s: Nicht genügend Crew vorhanden'),
                        $ship->getName()
                    ));
                    continue;
                }
                if ($ship->getReactorLoad() >= $ship->getReactorCapacity()) {
                    continue;
                }

                if ($this->reactorUtil->storageContainsNeededCommodities($ship->getStorage(), $hasWarpcore)) {
                    $loadMessage = $this->reactorUtil->loadReactor($ship, $requestedLoad, null, $hasWarpcore);

                    if ($loadMessage !== null) {
                        $game->addInformation($loadMessage);
                    }
                } else {
                    $game->addInformation(sprintf(
                        _('%s: Es werden mindestens folgende Waren zum Aufladen des %s benötigt:'),
                        $ship->getName(),
                        $hasWarpcore ? 'Warpkerns' : 'Fusionsreaktors'
                    ));
                    $costs = $hasWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;
                    foreach ($costs as $commodityId => $loadCost) {
                        $game->addInformation(sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId)));
                    }
                    continue;
                }
            }
            $game->addInformationMerge($msg);
            return;
        }
        $hasWarpcore = $ship->hasWarpcore();
        $hasFusionReactor = $ship->hasFusionReactor();

        if (!$hasWarpcore && !$hasFusionReactor) {
            $game->addInformation(_('Kein Reaktor vorhanden'));
            return;
        }
        if (!$ship->hasEnoughCrew()) {
            $game->addInformation(_('Nicht genügend Crew vorhanden'));
            return;
        }
        if ($ship->getReactorLoad() >= $ship->getReactorCapacity()) {
            $game->addInformationf(
                _('Der %s ist bereits vollständig geladen'),
                $hasWarpcore ? 'Warpkern' : 'Fusionsreaktor'
            );
            return;
        }
        if ($this->reactorUtil->storageContainsNeededCommodities($ship->getStorage(), $hasWarpcore)) {
            $loadMessage = $this->reactorUtil->loadReactor($ship, $requestedLoad, null, $hasWarpcore);

            if ($loadMessage !== null) {
                $game->addInformation($loadMessage);
            }
        } else {
            $game->addInformationf(
                _('Es werden mindestens folgende Waren zum Aufladen des %s benötigt:'),
                $hasWarpcore ? 'Warpkerns' : 'Fusionsreaktors'
            );
            $costs = $hasWarpcore ? ShipEnum::WARPCORE_LOAD_COST : ShipEnum::REACTOR_LOAD_COST;
            foreach ($costs as $commodityId => $loadCost) {
                $game->addInformation(sprintf(_('%d %s'), $loadCost, CommodityTypeEnum::getDescription($commodityId)));
            }
            return;
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
