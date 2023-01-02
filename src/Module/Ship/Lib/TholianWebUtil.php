<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
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

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        TholianWebRepositoryInterface $tholianWebRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->shipRepository = $shipRepository;
        $this->tholianWebRepository = $tholianWebRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->entityManager = $entityManager;
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

        $web->getCapturedShips()->removeElement($ship);

        if ($web->getCapturedShips()->isEmpty()) {
            $this->resetWebHelpers($web, $wrapper->getShipWrapperFactory());
            $this->removeWeb($web);
        }

        $ship->setHoldingWeb(null);
        $this->shipRepository->save($ship);
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
        if (empty($systems)) {
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
        $web = $emitter->getWebUnderConstruction();

        if ($emitter->ownedWebId === $emitter->webUnderConstructionId) {
            $emitter->setOwnedWebId(null);
        }

        $emitter->setWebUnderConstructionId(null)->update();
        $wrapper->getShipSystemManager()->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB, true);

        $ship = $wrapper->get();
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $this->shipRepository->save($ship);

        //update finish time last
        $this->updateWebFinishTime($web);
    }

    public function updateWebFinishTime(TholianWebInterface $web): void
    {
        //flush to read persistent webIds from system data
        $this->entityManager->flush();

        $targetWeightSum = array_reduce(
            $web->getCapturedShips()->toArray(),
            function (int $sum, ShipInterface $ship) {
                return $sum + $ship->getRump()->getTractorMass();
            },
            0
        );

        $webSpinnerWeightSum = array_reduce(
            $this->shipSystemRepository->getWebConstructingShipSystems($web->getId()),
            function (int $sum, ShipSystemInterface $shipSystem) {
                return $sum + $shipSystem->getShip()->getRump()->getTractorMass();
            },
            0
        );

        //only update if web spinners left
        if ($webSpinnerWeightSum !== 0) {
            $web->setFinishedTime(((int)ceil($targetWeightSum / $webSpinnerWeightSum)) * TimeConstants::ONE_HOUR_IN_SECONDS);
            $this->tholianWebRepository->save($web);
        }
    }
}
