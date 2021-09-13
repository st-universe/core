<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\BuildShipyardShip;

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
    public const ACTION_IDENTIFIER = 'B_BUILD_SHIPYARD_SHIP';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository;

    private ShipStorageManagerInterface $shipStorageManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipyardShipQueueRepositoryInterface $shipyardShipQueueRepository,
        ShipStorageManagerInterface $shipStorageManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipyardShipQueueRepository = $shipyardShipQueueRepository;
        $this->shipStorageManager = $shipStorageManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $shipyard = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $userId = $game->getUser()->getId();
        $shipyardId = $shipyard->getId();

        $plan = $this->shipBuildplanRepository->find(request::getIntFatal('planid'));

        if ($plan->getUser() !== $game->getUser()) {
            return;
        }

        $rump = $plan->getRump();
        if ($rump === null) {
            return;
        }

        if (!$shipyard->hasEnoughCrew()) {
            $game->addInformationf(_('Nicht genügend Crew vorhanden. (%d notwendig)'), $shipyard->getBuildplan()->getCrew());
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        if ($this->shipyardShipQueueRepository->getAmountByShipyard($shipyardId) > 0) {
            $game->addInformation(_('In dieser Werft wird bereits ein Schiff gebaut'));
            return;
        }

        if ($shipyard->getEps() < $rump->getEpsCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt, es ist jedoch nur %d Energie vorhanden'),
                $rump->getEpsCost(),
                $shipyard->getEps()
            );
            return;
        }

        $modules = $plan->getModules();

        $storage = $shipyard->getStorage();
        foreach ($modules as $moduleObj) {
            $module = $moduleObj->getModule();

            if (!$storage->containsKey($module->getGoodId())) {
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

        $shipyard->setEps($shipyard->getEps() - $rump->getEpsCost());

        $this->shipRepository->save($shipyard);
        $this->shipyardShipQueueRepository->save($queue);

        $game->addInformationf(
            _('Das Schiff der %s-Klasse wird gebaut - Fertigstellung: %s'),
            $rump->getName(),
            date("d.m.Y H:i", (time() + $plan->getBuildtime()))
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
