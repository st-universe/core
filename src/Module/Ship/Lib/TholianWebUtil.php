<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
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

    private StuTime $stuTime;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $loggerUtil;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        TholianWebRepositoryInterface $tholianWebRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        StuTime $stuTime,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->shipRepository = $shipRepository;
        $this->tholianWebRepository = $tholianWebRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->stuTime = $stuTime;
        $this->privateMessageSender = $privateMessageSender;
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

            //notify target owner
            $this->privateMessageSender->send(
                $web->getWebShip()->getUser()->getId(),
                $target->getUser()->getId(),
                sprintf(
                    'Das Energienetz um die %s in Sektor %s wurde aufgelöst',
                    $target->getName(),
                    $target->getSectorString()
                ),
                $target->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
    }

    public function removeWeb(TholianWebInterface $web): void
    {
        if ($web->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('removeWeb, webId: %d', $web->getId()));
        }
        $this->tholianWebRepository->delete($web);
        $this->shipRepository->delete($web->getWebShip());
    }

    public function releaseWebHelper(ShipWrapperInterface $wrapper): void
    {
        if ($wrapper->get()->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('releaseWebHelper, shipId: %d', $wrapper->get()->getId()));
        }

        $emitter = $wrapper->getWebEmitterSystemData();
        $web = $emitter->getWebUnderConstruction();

        $this->releaseWebHelperIntern($wrapper);

        $currentSpinnerSystems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());

        //remove web if lost
        if (empty($currentSpinnerSystems)) {
            $this->releaseAllShips($web, $wrapper->getShipWrapperFactory());
            $this->removeWeb($web);
        } else {
            $ship = $wrapper->get();

            //notify other web spinners
            foreach ($currentSpinnerSystems as $shipSystem) {
                $this->privateMessageSender->send(
                    $ship->getUser()->getId(),
                    $shipSystem->getShip()->getUser()->getId(),
                    sprintf(
                        'Die %s hat den Netzaufbau in Sektor %s verlassen, Fertigstellung: %s',
                        $ship->getName(),
                        $ship->getSectorString(),
                        $this->stuTime->transformToStuDate($web->getFinishedTime())
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );
            }
        }
    }

    public function resetWebHelpers(TholianWebInterface $web, ShipWrapperFactoryInterface $shipWrapperFactory, $isFinished = false): void
    {
        if ($web->getWebShip()->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('resetWebHelpers, webId: %d', $web->getId()));
        }
        $systems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());
        foreach ($systems as $system) {
            $wrapper = $shipWrapperFactory->wrapShip($system->getShip());
            $this->releaseWebHelperIntern($wrapper);

            //notify helpers when finished
            if ($isFinished) {
                $ship = $system->getShip();

                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $ship->getUser()->getId(),
                    sprintf(
                        'Das Energienetz in Sektor %s wurde fertiggestellt',
                        $ship->getSectorString()
                    ),
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
                );
            }
        }
    }

    private function releaseWebHelperIntern(ShipWrapperInterface $wrapper): void
    {
        $emitter = $wrapper->getWebEmitterSystemData();
        $web = $emitter->getWebUnderConstruction();

        if ($emitter->ownedWebId === $emitter->webUnderConstructionId && !$web->isFinished()) {
            $emitter->setOwnedWebId(null);
        }
        $emitter->setWebUnderConstructionId(null)->update();
        $wrapper->getShipSystemManager()->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB, true);

        $ship = $wrapper->get();
        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $this->shipRepository->save($ship);

        //update finish time last
        $this->updateWebFinishTime($web, -1);
    }

    public function updateWebFinishTime(TholianWebInterface $web, ?int $helperModifier = null): void
    {
        if ($web->getWebShip()->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('updateWebFinishTime, webId: %d', $web->getId()));
        }

        //flush to read persistent webIds from system data
        $this->entityManager->flush();

        if ($web->isFinished()) {
            return;
        }

        $currentSpinnerSystems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());
        ;
        $time = $this->stuTime->time();

        //adjust by modified web spinner count
        if ($helperModifier !== null) {
            $secondsLeft = $web->getFinishedTime() - $time;
            $currentSpinnerCount = count($currentSpinnerSystems);
            $oldSpinnerCount =  $currentSpinnerCount - $helperModifier;

            if ($currentSpinnerCount !== 0) {
                $web->setFinishedTime($time + (int)ceil($secondsLeft * $oldSpinnerCount / $currentSpinnerCount));
            }
            $this->tholianWebRepository->save($web);
            return;
        }

        //initialize by weight of targets and spinners
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

        if ($web->getWebShip()->getUser()->getId() === 126) {
            $this->loggerUtil->log(sprintf('targetWeightSum: %d, webSpinnerWeightSum: %d', $targetWeightSum, $webSpinnerWeightSum));
        }

        //only update if web spinners left
        if ($webSpinnerWeightSum !== 0) {
            $web->setFinishedTime($time + ((int)ceil($targetWeightSum / $webSpinnerWeightSum)) * TimeConstants::ONE_HOUR_IN_SECONDS);
            $this->tholianWebRepository->save($web);
        }
    }
}
