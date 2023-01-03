<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\LeaveFleetInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class FinishTholianWebs implements ProcessTickInterface
{
    private TholianWebRepositoryInterface $tholianWebRepository;

    private TholianWebUtilInterface $tholianWebUtil;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private LeaveFleetInterface $leaveFleet;

    private PrivateMessageSenderInterface $privateMessageSender;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        TholianWebRepositoryInterface $tholianWebRepository,
        TholianWebUtilInterface $tholianWebUtil,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LeaveFleetInterface $leaveFleet,
        PrivateMessageSenderInterface $privateMessageSender,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->tholianWebRepository = $tholianWebRepository;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->leaveFleet = $leaveFleet;
        $this->privateMessageSender = $privateMessageSender;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function work(): void
    {
        foreach ($this->tholianWebRepository->getFinishedWebs() as $web) {

            //remove captured ships from fleet
            $this->handleFleetConstellations($web);

            //free helper
            $this->tholianWebUtil->resetWebHelpers($web, $this->shipWrapperFactory);

            //set finished
            $web->setFinishedTime(null);
            $this->tholianWebRepository->save($web);
        }
    }

    private function handleFleetConstellations(TholianWebInterface $web): void
    {
        $fleets = [];

        //determine constellations
        foreach ($web->getCapturedShips() as $ship) {
            $fleet = $ship->getFleet();

            if ($fleet === null) {
                continue;
            }

            if (!array_key_exists($fleet->getId(), $fleets)) {
                $fleets[$fleet->getId()] = [];
            }

            $fleets[$fleet->getId()] = $ship;
        }

        $pms = [];

        //modify constellation
        foreach ($fleets as $shiplist) {
            /**
             * @var FleetInterface
             */
            $fleet = current($shiplist)->getFleet();

            //all ships of fleet in web, nothing to do
            if ($fleet->getShipCount() === count($shiplist)) {
                continue;
            }

            $userId = $fleet->getUser()->getId();
            $fleetName = $fleet->getName();

            /**
             * @var ShipInterface[] $shiplist
             */
            foreach ($shiplist as $ship) {
                $this->leaveFleet->leaveFleet($ship);

                if (!array_key_exists($userId, $pms)) {
                    $pms[$userId] = [];
                    $pms[$userId] = sprintf(_('Das Energienetz in Sektor %s wurde fertiggestellt') . "\n", $ship->getSectorString());;
                }

                $pms[$userId] .= sprintf('Die %s hat die Flotte %s verlassen' . "\n", $ship->getName(), $fleetName);
            }
        }

        //notify target owners
        foreach ($pms as $recipientId => $pm) {
            $this->privateMessageSender->send(
                $web->getUser()->getId(),
                $recipientId,
                $pm,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
    }
}
