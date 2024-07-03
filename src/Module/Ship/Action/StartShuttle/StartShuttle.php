<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\StartShuttle;

use Override;
use request;
use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class StartShuttle implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_SHUTTLE';

    public function __construct(private ShipRepositoryInterface $shipRepository, private ShipLoaderInterface $shipLoader, private ShipCreatorInterface $shipCreator, private ShipBuildplanRepositoryInterface $shipBuildplanRepository, private ShipStorageManagerInterface $shipStorageManager, private TroopTransferUtilityInterface $troopTransferUtility, private ActivatorDeactivatorHelperInterface $helper)
    {
    }

    #[Override]
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
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if ($ship->getShieldState()) {
            $game->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }

        // check if ship storage contains shuttle commodity
        $storage = $ship->getStorage();

        $rumpCommodity = $rump->getCommodity();
        if ($rumpCommodity !== null && !$storage->containsKey($rumpCommodity->getId())) {
            $game->addInformationf(
                _('Es wird %d %s benötigt'),
                1,
                $rumpCommodity->getName()
            );
            return;
        }

        // check if ship has excess crew
        if ($ship->getExcessCrewCount() < $plan->getCrew()) {
            $game->addInformation(sprintf(_('Es werden %d freie Crewman für den Start des %s benötigt'), $plan->getCrew(), $rump->getName()));
            return;
        }

        // check if ship got enough energy
        if ($epsSystem->getEps() < $rump->getBaseEps()) {
            $game->addInformation(sprintf(_('Es wird %d Energie für den Start des %s benötigt'), $rump->getBaseEps(), $rump->getName()));
            return;
        }

        // remove shuttle from storage
        if ($rumpCommodity !== null) {
            $this->shipStorageManager->lowerStorage(
                $ship,
                $rumpCommodity,
                1
            );
        }

        // start shuttle and transfer crew
        $this->startShuttle($ship, $epsSystem, $plan, $game);

        $game->addInformation(sprintf(_('%s wurde erfolgreich gestartet'), $rump->getName()));
    }

    private function startShuttle(
        ShipInterface $ship,
        EpsSystemData $epsSystem,
        ShipBuildplanInterface $plan,
        GameControllerInterface $game
    ): void {
        $rump = $plan->getRump();

        $shuttleWrapper = $this->shipCreator->createBy(
            $ship->getUser()->getId(),
            $rump->getId(),
            $plan->getId()
        )
            ->setLocation($ship->getLocation())
            ->loadWarpdrive(100)
            ->finishConfiguration();

        $shuttleEps = $shuttleWrapper->getEpsSystemData();
        if ($shuttleEps !== null) {
            $shuttleEps->setEps($shuttleEps->getMaxEps())->update();
            $epsSystem->lowerEps($shuttleEps->getMaxEps())->update();
        }

        $shuttle = $shuttleWrapper->get();
        $shuttle->getShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);

        $shipCrewArray = $ship->getCrewAssignments()->getValues();
        for ($i = 0; $i < $plan->getCrew(); $i++) {
            $this->troopTransferUtility->assignCrew($shipCrewArray[$i], $shuttle);
        }

        $this->shipRepository->save($shuttle);

        if (
            $ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $ship->getSystemState(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)
            && $ship->getExcessCrewCount() <= 0
        ) {
            $this->helper->deactivate($ship->getId(), ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game);
        }
        $this->shipRepository->save($ship);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
