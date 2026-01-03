<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\BuildStation;

use request;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Station\StationLocationEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Station;
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

    #[\Override]
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
            $game->getInfo()->addInformation('Bitte Stationstyp auswählen');
            return;
        }

        $plan = $this->stationUtility->getBuidplanIfResearchedByUser($wantedPlanId, $userId);
        if ($plan === null) {
            return;
        }

        $rump = $plan->getRump();
        $role = $rump->getRoleId();
        if ($role === null) {
            throw new RuntimeException(sprintf('No rump role for rumpId %d, planId %d', $rump->getId(), $wantedPlanId));
        }

        // check if the limit is reached
        $limit = $role->getBuildLimit();
        if ($this->spacecraftRepository->getAmountByUserAndRump($userId, $rump->getId()) >= $limit) {
            $game->getInfo()->addInformation(sprintf(_('Es können nur %d %s errichtet werden'), $limit, $rump->getName()));
            return;
        }

        // check if the location is allowed
        $location = $role->getPossibleBuildLocations();
        if (!$this->isLocationAllowed($station, $location)) {
            $game->getInfo()->addInformation(sprintf(_('Stationen vom Typ %s können nur %s errichtet werden'), $rump->getName(), $location->value));
            return;
        }

        // check if enough workbees
        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $rump)) {
            $game->getInfo()->addInformation('Nicht genügend Workbees angedockt');
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
            $game->getInfo()->addInformation('Nicht alle erforderlichen Module geladen');
            return;
        }

        // transform construction
        $this->startTransformation($station, $plan, $wantedSpecialModules);

        $game->getInfo()->addInformation(sprintf(
            _('%s befindet sich nun im Bau. Fertigstellung bestenfalls in %d Ticks'),
            $rump->getName(),
            $rump->getBuildtime()
        ));
    }

    private function isLocationAllowed(Station $station, StationLocationEnum $location): bool
    {
        if ($location === StationLocationEnum::BUILDABLE_EVERYWHERE) {
            return true;
        }

        $inSystem = $station->getSystem();
        if ($inSystem && $location === StationLocationEnum::BUILDABLE_INSIDE_SYSTEM) {
            return true;
        }

        $overSystem = $station->isOverSystem();
        if ($overSystem && ($location === StationLocationEnum::BUILDABLE_OVER_SYSTEM
            ||  $location === StationLocationEnum::BUILDABLE_OUTSIDE_SYSTEM)) {
            return true;
        }

        $outsideSystem = !$inSystem && !$overSystem;
        return $outsideSystem && $location === StationLocationEnum::BUILDABLE_OUTSIDE_SYSTEM;
    }

    /**
     * @param array<Module> $wantedSpecialModules
     */
    private function startTransformation(
        Station $station,
        SpacecraftBuildplan $plan,
        array $wantedSpecialModules
    ): void {
        $rump = $plan->getRump();

        $baseHull = $rump->getBaseValues()->getBaseHull();

        $station->setName(sprintf('%s in Bau', $rump->getName()));
        $station->setMaxHull($baseHull);
        $station->setRump($rump);
        $station->setBuildplan($plan);
        $station->getCondition()->setHull(intdiv($baseHull, 2));
        $station->getCondition()->setState(SpacecraftStateEnum::UNDER_CONSTRUCTION);

        $progress = $station->getConstructionProgress() ?? $this->constructionProgressRepository->prototype();
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
     * @param array<Module> $availableMods
     */
    private function getModuleIfAllowed(int $wantedModId, array $availableMods): ?Module
    {
        foreach ($availableMods as $mod) {
            if ($mod->getId() === $wantedModId) {
                return $mod;
            }
        }

        return null;
    }

    /**
     * @return array<Module>
     */
    private function getSpecialModules(Station $station, SpacecraftRump $rump): array
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
     * @param array<Module> $wantedSpecialModules
     */
    public function consumeNeededModules(
        Station $station,
        SpacecraftBuildplan $plan,
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

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
