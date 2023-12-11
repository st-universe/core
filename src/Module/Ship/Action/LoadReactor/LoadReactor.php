<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LoadReactor;

use request;
use RuntimeException;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ReactorUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LoadReactor implements ActionControllerInterface
{
    /**
     * @var ShipRepositoryInterface
     */
    public $shipRepository;
    public const ACTION_IDENTIFIER = 'B_LOAD_REACTOR';

    private ShipLoaderInterface $shipLoader;

    private ReactorUtilInterface $reactorUtil;

    private CommodityCacheInterface $commodityCache;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ReactorUtilInterface $reactorUtil,
        ShipRepositoryInterface $shipRepository,
        CommodityCacheInterface $commodityCache
    ) {
        $this->shipLoader = $shipLoader;
        $this->reactorUtil = $reactorUtil;
        $this->shipRepository = $shipRepository;
        $this->commodityCache = $commodityCache;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $requestedLoad = request::postIntFatal('reactorload');

        if (request::postString('fleet_reactor') !== false) {
            $msg = [];
            $msg[] = _('Flottenbefehl ausgeführt: Aufladung des Reaktors');

            $fleetWrapper = $wrapper->getFleetWrapper();
            if ($fleetWrapper === null) {
                throw new RuntimeException('this should not happen');
            }

            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {

                $ship = $wrapper->get();
                $reactor = $wrapper->getReactorWrapper();

                if ($reactor === null) {
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
                if ($reactor->getLoad() >= $reactor->getCapacity()) {
                    continue;
                }

                if ($this->reactorUtil->storageContainsNeededCommodities($ship->getStorage(), $reactor)) {
                    $loadMessage = $this->reactorUtil->loadReactor($ship, $requestedLoad, null, $reactor);

                    if ($loadMessage !== null) {
                        $game->addInformation($loadMessage);
                    }
                } else {
                    $game->addInformation(sprintf(
                        _('%s: Es werden mindestens folgende Waren zum Aufladen des %ss benötigt:'),
                        $ship->getName(),
                        $reactor->get()->getSystemType()->getDescription()
                    ));

                    foreach ($reactor->get()->getLoadCost() as $commodityId => $loadCost) {
                        $commodity = $this->commodityCache->get($commodityId);
                        $game->addInformation(sprintf(_('%d %s'), $loadCost, $commodity->getName()));
                    }
                    continue;
                }
            }
            $game->addInformationMerge($msg);
            return;
        }

        $reactor = $wrapper->getReactorWrapper();

        if ($reactor === null) {
            $game->addInformation(_('Kein Reaktor vorhanden'));
            return;
        }

        $ship = $wrapper->get();
        if (!$ship->hasEnoughCrew()) {
            $game->addInformation(_('Nicht genügend Crew vorhanden'));
            return;
        }

        $systemName = $reactor->get()->getSystemType()->getDescription();

        if ($reactor->getLoad() >= $reactor->getCapacity()) {
            $game->addInformationf(
                _('Der %s ist bereits vollständig geladen'),
                $systemName
            );
            return;
        }
        if ($this->reactorUtil->storageContainsNeededCommodities($ship->getStorage(), $reactor)) {
            $loadMessage = $this->reactorUtil->loadReactor($ship, $requestedLoad, null, $reactor);

            if ($loadMessage !== null) {
                $game->addInformation($loadMessage);
            }
        } else {
            $game->addInformationf(
                _('Es werden mindestens folgende Waren zum Aufladen des %ss benötigt:'),
                $systemName
            );

            foreach ($reactor->get()->getLoadCost() as $commodityId => $loadCost) {
                $commodity = $this->commodityCache->get($commodityId);
                $game->addInformation(sprintf(_('%d %s'), $loadCost, $commodity->getName()));
            }
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
