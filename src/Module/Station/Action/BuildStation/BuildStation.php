<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\BuildStation;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Station\StationEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class BuildStation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILD_STATION';

    public function __construct(
        private StationUtilityInterface $stationUtility,
        private StationLoaderInterface $stationLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private StorageManagerInterface $storageManager,
        private ConstructionProgressRepositoryInterface $constructionProgressRepository,
        private ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $station = $this->stationLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $userId = $game->getUser()->getId();

        $wantedPlanId = request::postInt('plan_select');

        if ($wantedPlanId === 0) {
            $game->addInformation('Bitte Stationstyp auswählen');
            return;
        }

        $plan = $this->stationUtility->getBuidplanIfResearchedByUser($wantedPlanId, $userId);
        if ($plan === null) {
            return;
        }

        $rump = $plan->getRump();

        // check if the limit is reached
        $limit = StationEnum::BUILDABLE_LIMITS_PER_ROLE[$rump->getRoleId()];
        if ($this->spacecraftRepository->getAmountByUserAndRump($userId, $rump->getId()) >= $limit) {
            $game->addInformation(sprintf(_('Es können nur %d %s errichtet werden'), $limit, $rump->getName()));
            return;
        }

        // check if the location is allowed
        $location = StationEnum::BUILDABLE_LOCATIONS_PER_ROLE[$rump->getRoleId()];
        if (!$this->locationAllowed($station, $location)) {
            $game->addInformation(sprintf(_('Stationen vom Typ %s können nur %s errichtet werden'), $rump->getName(), $location));
            return;
        }

        // check if enough workbees
        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $rump)) {
            $game->addInformation('Nicht genügend Workbees angedockt');
            return;
        }

        $availableMods = $this->getSpecialModules($station, $rump);

        // check if special modules allowed
        $wantedSpecialModuleIds = request::postArray('mod_9');
        $wantedSpecialModules = [];
        foreach ($wantedSpecialModuleIds as $wantedModId) {
            $mod = $this->getModuleIfAllowed((int) $wantedModId, $availableMods);

            if ($mod === null) {
                return;
            } else {
                $wantedSpecialModules[] = $mod;
            }
        }

        // try to consume needed commodities
        if (!$this->consumeNeededModules($station, $plan, $wantedSpecialModules)) {
            $game->addInformation('Nicht alle erforderlichen Module geladen');
            return;
        }

        // transform construction
        $this->startTransformation($station, $plan, $wantedSpecialModules);

        $game->addInformation(sprintf(
            _('%s befindet sich nun im Bau. Fertigstellung bestenfalls in %d Ticks'),
            $rump->getName(),
            $rump->getBuildtime()
        ));
    }

    private function locationAllowed(StationInterface $station, string $location): bool
    {
        if ($location === StationEnum::BUILDABLE_EVERYWHERE) {
            return true;
        }

        $inSystem = $station->getSystem();
        if ($inSystem && $location === StationEnum::BUILDABLE_INSIDE_SYSTEM) {
            return true;
        }

        $overSystem = $station->isOverSystem();
        if ($overSystem && ($location === StationEnum::BUILDABLE_OVER_SYSTEM
            ||  $location === StationEnum::BUILDABLE_OUTSIDE_SYSTEM)) {
            return true;
        }

        $outsideSystem = !$inSystem && !$overSystem;
        return $outsideSystem && $location === StationEnum::BUILDABLE_OUTSIDE_SYSTEM;
    }

    /**
     * @param array<ModuleInterface> $wantedSpecialModules
     */
    private function startTransformation(
        StationInterface $station,
        SpacecraftBuildplanInterface $plan,
        array $wantedSpecialModules
    ): void {
        $rump = $plan->getRump();

        $station->setName(sprintf('%s in Bau', $rump->getName()));
        $station->setHuell(intdiv($rump->getBaseHull(), 2));
        $station->setMaxHuell($rump->getBaseHull());
        $station->setRump($rump);
        $station->setBuildplan($plan);
        $station->setState(SpacecraftStateEnum::SHIP_STATE_UNDER_CONSTRUCTION);

        $this->spacecraftRepository->save($station);

        $progress = $this->constructionProgressRepository->getByStation($station);

        if ($progress === null) {
            $progress = $this->constructionProgressRepository->prototype();
        }
        $progress->setStation($station);
        $progress->setRemainingTicks($rump->getBuildtime());

        $this->constructionProgressRepository->save($progress);

        foreach ($wantedSpecialModules as $mod) {
            $progressModule = $this->constructionProgressModuleRepository->prototype();
            $progressModule->setConstructionProgress($progress);
            $progressModule->setModule($mod);

            $this->constructionProgressModuleRepository->save($progressModule);
        }
    }

    /**
     * @param array<ModuleInterface> $availableMods
     */
    private function getModuleIfAllowed(int $wantedModId, array $availableMods): ?ModuleInterface
    {
        foreach ($availableMods as $mod) {
            if ($mod->getId() === $wantedModId) {
                return $mod;
            }
        }

        return null;
    }

    /**
     * @return array<ModuleInterface>
     */
    private function getSpecialModules(StationInterface $station, SpacecraftRumpInterface $rump): array
    {
        $shipRumpRole = $rump->getShipRumpRole();
        if ($shipRumpRole === null) {
            return [];
        }

        return $this->moduleRepository->getBySpecialTypeAndRump(
            $station,
            SpacecraftModuleTypeEnum::SPECIAL,
            $rump->getId()
        );
    }

    /**
     * @param array<ModuleInterface> $wantedSpecialModules
     */
    public function consumeNeededModules(
        StationInterface $station,
        SpacecraftBuildplanInterface $plan,
        array $wantedSpecialModules
    ): bool {
        // check if everything is available in required numbers
        foreach ($plan->getModules() as $buildplanModule) {
            $commodity = $buildplanModule->getModule()->getCommodity();

            $stor = $station->getStorage()[$commodity->getId()];

            if ($stor === null || $stor->getAmount() < $buildplanModule->getModuleCount()) {
                return false;
            }
        }
        foreach ($wantedSpecialModules as $mod) {
            $stor = $station->getStorage()[$mod->getCommodity()->getId()];

            if ($stor === null) {
                return false;
            }
        }

        // consume the module commodities
        foreach ($plan->getModules() as $buildplanModule) {
            $commodity = $buildplanModule->getModule()->getCommodity();

            $this->storageManager->lowerStorage($station, $commodity, $buildplanModule->getModuleCount());
        }
        foreach ($wantedSpecialModules as $mod) {
            $this->storageManager->lowerStorage($station, $mod->getCommodity(), 1);
        }

        return true;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
