<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class TholianWebUtil implements TholianWebUtilInterface
{
    private ShipRepositoryInterface $shipRepository;

    private TholianWebRepositoryInterface $tholianWebRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        TholianWebRepositoryInterface $tholianWebRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipRepository = $shipRepository;
        $this->tholianWebRepository = $tholianWebRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->loggerUtil->init('WEB', LoggerEnum::LEVEL_WARNING);
    }

    public function releaseShipFromWeb(ShipWrapperInterface $wrapper): void
    {
        if ($wrapper->get()->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('releaseShipFromWeb, shipId: %d', $wrapper->get()->getId()));
        }
        $ship = $wrapper->get();
        $web = $ship->getHoldingWeb();

        $ship->setHoldingWeb(null);
        $this->shipRepository->save($ship);
        $web->getCapturedShips()->removeElement($ship);

        if ($web->getCapturedShips()->isEmpty()) {
            $this->resetWebHelpers($web, $wrapper->getShipWrapperFactory());
            $this->removeWeb($web);
        }
    }

    public function releaseAllShips(TholianWebInterface $web, ShipWrapperFactoryInterface $shipWrapperFactory): void
    {
        foreach ($web->getCapturedShips() as $target) {
            $this->releaseShipFromWeb($shipWrapperFactory->wrapShip($target));
        }
    }

    public function removeWeb(TholianWebInterface $web): void
    {
        if ($web->getWebShip()->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('removeWeb, webId: %d', $web->getId()));
        }
        $this->tholianWebRepository->delete($web);
        $this->shipRepository->delete($web->getWebShip());
    }

    public function releaseWebHelper(ShipWrapperInterface $wrapper): void
    {
        $emitter = $wrapper->getWebEmitterSystemData();
        $web = $emitter->getWebUnderConstruction();

        $this->releaseWebHelperIntern($wrapper);

        $systems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());

        //remove web if lost
        if (count($systems) === 1) {
            $this->releaseAllShips($web, $wrapper->getShipWrapperFactory());
            $this->removeWeb($web);
        }
    }

    public function resetWebHelpers(TholianWebInterface $web, ShipWrapperFactoryInterface $shipWrapperFactory): void
    {
        if ($web->getWebShip()->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('resetWebHelpers, webId: %d', $web->getId()));
        }
        $systems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());
        foreach ($systems as $system) {
            $wrapper = $shipWrapperFactory->wrapShip($system->getShip());
            $this->releaseWebHelperIntern($wrapper);
        }
    }

    private function releaseWebHelperIntern(ShipWrapperInterface $wrapper): void
    {
        $emitter = $wrapper->getWebEmitterSystemData();

        if ($emitter->ownedWebId === $emitter->webUnderConstructionId) {
            $emitter->setOwnedWebId(null);
        }

        $emitter->setWebUnderConstructionId(null)->update();
        $wrapper->getShipSystemManager()->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB, true);
    }
}
