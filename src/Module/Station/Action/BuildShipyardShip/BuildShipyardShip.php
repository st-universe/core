<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\BuildShipyardShip;

use Override;
use request;

use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class BuildShipyardShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILD_SHIPYARD_SHIP';

    public function __construct(
        private StationLoaderInterface $stationLoader,
        private StationRepositoryInterface $stationRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        private StorageManagerInterface $storageManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->stationLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $shipyard = $wrapper->get();

        $userId = $game->getUser()->getId();
        $shipyardId = $shipyard->getId();

        $plan = $this->spacecraftBuildplanRepository->find(request::getIntFatal('planid'));
        if ($plan === null) {
            return;
        }

        if ($plan->getUser() !== $game->getUser()) {
            return;
        }

        if (!$shipyard->hasEnoughCrew($game)) {
            return;
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        if ($this->shipyardShipQueueRepository->getAmountByShipyard($shipyardId) > 0) {
            $game->addInformation(_('In dieser Werft wird bereits ein Schiff gebaut'));
            return;
        }

        $rump = $plan->getRump();

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < $rump->getEpsCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt, es ist jedoch nur %d Energie vorhanden'),
                $rump->getEpsCost(),
                $epsSystem === null ? 0 : $epsSystem->getEps()
            );
            return;
        }

        $modules = $plan->getModules();

        $storage = $shipyard->getStorage();
        foreach ($modules as $moduleObj) {
            $module = $moduleObj->getModule();

            if (!$storage->containsKey($module->getCommodityId())) {
                $game->addInformationf(_('Es wird 1 %s benötigt'), $module->getName());
                return;
            }
        }

        foreach ($modules as $moduleObj) {
            $module = $moduleObj->getModule();

            $this->storageManager->lowerStorage($shipyard, $module->getCommodity(), 1);
        }

        $queue = $this->shipyardShipQueueRepository->prototype();
        $queue->setStation($shipyard);
        $queue->setUserId($userId);
        $queue->setRump($rump);
        $queue->setSpacecraftBuildplan($plan);
        $queue->setBuildtime($plan->getBuildtime());
        $queue->setFinishDate(time() + $plan->getBuildtime());

        $epsSystem->lowerEps($rump->getEpsCost())->update();

        $this->stationRepository->save($shipyard);
        $this->shipyardShipQueueRepository->save($queue);

        $game->addInformationf(
            _('Das Schiff der %s-Klasse wird gebaut - Fertigstellung: %s'),
            $rump->getName(),
            date("d.m.Y H:i", (time() + $plan->getBuildtime()))
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
