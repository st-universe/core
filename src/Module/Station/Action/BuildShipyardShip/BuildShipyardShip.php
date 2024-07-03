<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\BuildShipyardShip;

use Override;
use request;

use Stu\Component\Ship\Storage\ShipStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipyardShipQueueRepositoryInterface;

final class BuildShipyardShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BUILD_SHIPYARD_SHIP';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShipRepositoryInterface $shipRepository, private ShipBuildplanRepositoryInterface $shipBuildplanRepository, private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository, private ShipStorageManagerInterface $shipStorageManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $shipyard = $wrapper->get();

        $userId = $game->getUser()->getId();
        $shipyardId = $shipyard->getId();

        $plan = $this->shipBuildplanRepository->find(request::getIntFatal('planid'));
        if ($plan === null) {
            return;
        }

        if ($plan->getUser() !== $game->getUser()) {
            return;
        }

        if (!$shipyard->hasEnoughCrew($game)) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

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

            $this->shipStorageManager->lowerStorage($shipyard, $module->getCommodity(), 1);
        }

        $queue = $this->shipyardShipQueueRepository->prototype();
        $queue->setShip($shipyard);
        $queue->setUserId($userId);
        $queue->setRump($rump);
        $queue->setShipBuildplan($plan);
        $queue->setBuildtime($plan->getBuildtime());
        $queue->setFinishDate(time() + $plan->getBuildtime());

        $epsSystem->lowerEps($rump->getEpsCost())->update();

        $this->shipRepository->save($shipyard);
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
