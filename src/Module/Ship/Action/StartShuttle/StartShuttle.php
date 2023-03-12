<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartShuttle;

use request;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class StartShuttle implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_START_SHUTTLE';

    private ShipRepositoryInterface $shipRepository;

    private ShipLoaderInterface $shipLoader;

    private ShipCreatorInterface $shipCreator;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private CommodityRepositoryInterface $commodityRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        ShipLoaderInterface $shipLoader,
        ShipCreatorInterface $shipCreator,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        CommodityRepositoryInterface $commodityRepository,
        ShipStorageManagerInterface $shipStorageManager,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipRepository = $shipRepository;
        $this->shipLoader = $shipLoader;
        $this->shipCreator = $shipCreator;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->commodityRepository = $commodityRepository;
        $this->shipStorageManager = $shipStorageManager;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $commodityId = request::postIntFatal('shid');

        $plan = $this->shipBuildplanRepository->getShuttleBuildplan($commodityId);

        if ($plan === null) {
            return;
        }

        $rump = $plan->getRump();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if (!$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        // check if ship storage contains shuttle commodity
        $storage = $ship->getStorage();

        if (!$storage->containsKey($rump->getCommodityId())) {
            $game->addInformationf(
                _('Es wird %d %s benötigt'),
                1,
                $this->commodityRepository->find((int) $rump->getCommodityId())->getName()
            );
            return;
        }

        // check if ship has excess crew
        if ($ship->getCrewCount() - $ship->getBuildplan()->getCrew() < $plan->getCrew()) {
            $game->addInformation(sprintf(_('Es werden %d freie Crewman für den Start des %s benötigt'), $plan->getCrew(), $rump->getName()));
            return;
        }

        // check if ship got enough energy
        if ($epsSystem->getEps() < $rump->getBaseEps()) {
            $game->addInformation(sprintf(_('Es wird %d Energie für den Start des %s benötigt'), $rump->getBaseEps(), $rump->getName()));
            return;
        }

        // remove shuttle from storage
        $this->shipStorageManager->lowerStorage(
            $ship,
            $rump->getCommodity(),
            1
        );

        // start shuttle and transfer crew
        $this->startShuttle($wrapper, $plan, $game);

        $game->addInformation(sprintf(_('%s wurde erfolgreich gestartet'), $rump->getName()));
    }

    private function startShuttle(ShipWrapperInterface $wrapper, ShipBuildplanInterface $plan, GameControllerInterface $game): void
    {
        $ship = $wrapper->get();
        $rump = $plan->getRump();

        $shuttleWrapper = $this->shipCreator->createBy(
            $ship->getUser()->getId(),
            $rump->getId(),
            $plan->getId()
        );

        $shuttleEps = $shuttleWrapper->getEpsSystemData();
        $shuttleEps->setEps($shuttleEps->getMaxEps())->update();

        $shuttle = $shuttleWrapper->get();
        $shuttle->getShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);

        $shuttle->updateLocation($ship->getMap(), $ship->getStarsystemMap());

        $shipCrewArray = $ship->getCrewlist()->getValues();
        for ($i = 0; $i < $plan->getCrew(); $i++) {
            $shipCrew = $shipCrewArray[$i];
            $shipCrew->setShip($shuttle);
            $ship->getCrewlist()->removeElement($shipCrew);

            $this->shipCrewRepository->save($shipCrew);
        }

        $this->shipRepository->save($shuttle);

        $epsSystem = $wrapper->getEpsSystemData();
        $epsSystem->setEps($epsSystem->getEps() - $shuttleEps->getMaxEps())->update();
        if (
            $ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $ship->getSystemState(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            &&  $ship->getCrewCount() <= $ship->getBuildplan()->getCrew()
        ) {
            $this->helper->deactivate($ship->getId(), ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game);
        }
        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
