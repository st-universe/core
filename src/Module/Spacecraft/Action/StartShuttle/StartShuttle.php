<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StartShuttle;

use Override;
use request;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class StartShuttle implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_SHUTTLE';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ShipCreatorInterface $shipCreator,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private StorageManagerInterface $storageManager,
        private TroopTransferUtilityInterface $troopTransferUtility,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $ship = $wrapper->get();

        $commodityId = request::postIntFatal('shid');

        $plan = $this->spacecraftBuildplanRepository->getShuttleBuildplan($commodityId);

        if ($plan === null) {
            return;
        }

        $rump = $plan->getRump();

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if (!$ship->isSystemHealthy(SpacecraftSystemTypeEnum::SHUTTLE_RAMP)) {
            $game->addInformation(_("Die Shuttle-Rampe ist zerstört"));
            return;
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() == 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($ship->isCloaked()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
            return;
        }
        if ($ship->isShielded()) {
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
            $this->storageManager->lowerStorage(
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
        SpacecraftInterface $ship,
        EpsSystemData $epsSystem,
        SpacecraftBuildplanInterface $plan,
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
        $shuttle->getSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT)->setMode(SpacecraftSystemModeEnum::MODE_ALWAYS_ON);

        $shipCrewArray = $ship->getCrewAssignments()->getValues();
        for ($i = 0; $i < $plan->getCrew(); $i++) {
            $this->troopTransferUtility->assignCrew($shipCrewArray[$i], $shuttle);
        }

        $this->spacecraftRepository->save($shuttle);

        if (
            $ship->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            && $ship->getSystemState(SpacecraftSystemTypeEnum::TROOP_QUARTERS)
            && $ship->getExcessCrewCount() <= 0
        ) {
            $this->helper->deactivate($ship->getId(), SpacecraftSystemTypeEnum::TROOP_QUARTERS, $game);
        }
        $this->spacecraftRepository->save($ship);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
